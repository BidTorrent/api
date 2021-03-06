<?php
require_once('vendor/redmap/main/src/schema.php');
require_once('vendor/redmap/main/src/drivers/mysqli.php');

class Bidders
{
    public $db;
    public $bidderSchema;
    public $filterSchema;
    public $users;

    static $BIDDER_FIELDS = array('name', 'bidUrl', 'rsaPubKey');
    static $FILTER_FIELDS = array('bidder', 'type');

    function __construct($db, $users)
    {
        $this->db = $db;
        $this->users = $users;
        $this->bidderSchema = new RedMap\Schema
        (
            'bidders',
            array
            (
                'id'    => array (RedMap\Schema::FIELD_PRIMARY),
                'name'  => null,
                'bidUrl' => null,
                'rsaPubKey' => null,
                'sampling' => null
            )
        );
        $this->filterSchema = new RedMap\Schema
        (
            'bidders_filters',
            array
            (
                'type'    => array (RedMap\Schema::FIELD_PRIMARY),
                'bidder'  => array (RedMap\Schema::FIELD_PRIMARY),
                'mode'  => null,
                'value'  => null
            )
        );
    }

    function myBidders($userId) {
        if ($userId === null)
            return array();

        list($allAccess, $bidderIds) = $this->users->myBidders($userId);

        if ($allAccess) {
            list ($query, $params) = $this->bidderSchema->get();
            $rows = $this->db->get_rows($query, $params);
        }
        else {
            list ($query, $params) = $this->bidderSchema->get(array ('id|in' => $bidderIds));
            $rows = $this->db->get_rows($query, $params);
        }

        if ($rows === null)
            return array();

        return array_map(function($row) { return array('id' => (int)$row['id'], 'name' => $row['name']);}, $rows);
    }

    function getAll($app, $uiFormat)
    {
        // Get filters
        list ($filtersQuery, $filtersParams) = $this->filterSchema->get();
        $filterRows = $this->db->get_rows($filtersQuery, $filtersParams);

        $filters = array_map(function($row) { return new BidderFilter($row); }, $filterRows);
        $biddersFilters = array();
        foreach ($filters as $filter)
        {
            $bidderFilters = array();
            if (isset($biddersFilters[$filter->bidder]))
                $bidderFilters = $biddersFilters[$filter->bidder];

            array_push($bidderFilters, $filter);
            $biddersFilters[$filter->bidder] = $bidderFilters;
        }

        // Get bidders
        list ($query, $params) = $this->bidderSchema->get();
        $rows = $this->db->get_rows($query, $params);

        // Format the response
        $result = array();
        foreach ($rows as $row) {
            $filters = array();
            $bidderId = (int)$row['id'];

            if (isset($biddersFilters[$bidderId]))
                $filters = $biddersFilters[$bidderId];

            $bidder = new Bidder($row, $filters);
            if ($uiFormat)
                array_push($result, $bidder);
            else
                array_push($result, $this->_format($bidder));
        }

        return $result;
    }

    function get($app, $id, $uiFormat)
    {
        // Get filters
        list ($filtersQuery, $filtersParams) = $this->filterSchema->get(array ('bidder' => $id));
        $filterRows = $this->db->get_rows($filtersQuery, $filtersParams);
        $filters = array_map(function($row) { return new BidderFilter($row); }, $filterRows);

        // Get bidder
        list ($query, $params) = $this->bidderSchema->get(array ('id' => $id));
        $row = $this->db->get_first($query, $params);

        if (!isset($row))
            $app->halt(404);

        $bidder = new Bidder($row, $filters);
        if ($uiFormat)
            return $bidder;

        return $this->_format($bidder);
    }

    // Format the bidder to match the config needed by the client script
    private function _format($bidder)
    {
        $filters = array();

        if ($bidder->sampling != 100)
            $filters['sampling'] = $bidder->sampling;

        $config = array();
        $config['id'] = $bidder->id;
        $config['bid_ep'] = $bidder->bidUrl;
        $config['key'] = $bidder->rsaPubKey;
        $config['name'] = $bidder->name;

        $filters = $this->_getFiltersConfig($bidder, array
        (
            'publisher_domain' => 'pub',
            'publisher_country' => 'pub_ctry',
            'user_country' => 'user_ctry',
            'iab_category' => 'cat'
        ));

        if (isset($filters) && count($filters) > 0)
            $config['filters'] = $filters;

        return $config;
    }

    private function _getFiltersConfig($bidder, $filterTypesAndKeys)
    {
        $config = array();
        foreach ($bidder->filters as $filter)
        {
            if (isset($filterTypesAndKeys[$filter->type]))
            {
                $key = $filterTypesAndKeys[$filter->type];
                $config[$key] = $filter->value;

                if ($filter->mode == 'inclusive')
                    $config[$key . '_wl'] = true;
            }
        }

        if ($bidder->sampling < 100)
            $config['sampling'] = $bidder->sampling;

        return $config;
    }

    function post($app, $userId)
    {
        if ($userId === null)
            $app->halt(401);

        list($bidder, $filters) = $this->_getRequestParameters($app);

        if (!$this->_validate($bidder, bidders::$BIDDER_FIELDS))
            $app->halt(400);

        // Add bidder
        $this->db->execute('START TRANSACTION');
        list ($query, $params) = $this->bidderSchema->set(RedMap\Schema::SET_INSERT, $bidder);
        $insertedBidderId = $this->db->insert($query, $params);

        if (!isset($insertedBidderId))
        {
            $this->db->execute('ROLLBACK');
            $app->halt(409);
        }

        // Add filters
        foreach ($filters as $filter)
            $this->_addFilter($app, $filter, $insertedBidderId);

        // Add bidder<->user relation
        if (!$this->users->addUserForBidder($userId, $insertedBidderId))
        {
            $this->db->execute('ROLLBACK');
            $app->halt(500);
        }

        $this->db->execute('COMMIT');

        return array('id' => $insertedBidderId);
    }

    function put($app, $id)
    {
        list($bidder, $filters) = $this->_getRequestParameters($app);
        $bidder['id'] = $id;

        // Get bidder
        list ($query, $params) = $this->bidderSchema->get(array ('id' => $id));
        $row = $this->db->get_first($query, $params);

        if (!isset($row))
            $app->halt(404);

        // Update bidder
        $this->db->execute('START TRANSACTION');
        list ($query, $params) = $this->bidderSchema->set(RedMap\Schema::SET_UPDATE, $bidder);
        $bidderUpdateResult = $this->db->execute($query, $params);

        if (!isset($bidderUpdateResult))
            $app->halt(500);

        // Delete bidder's filters
        list ($query, $params) = $this->filterSchema->delete(array ('bidder' => $id));
        $this->db->execute($query, $params);

        // Add filters
        foreach ($filters as $filter)
            $this->_addFilter($app, $filter, $id);

        $this->db->execute('COMMIT');
    }

    function delete($app, $userId, $id)
    {
        // Delete bidder's filters
        list ($query, $params) = $this->filterSchema->delete(array ('bidder' => $id));
        $this->db->execute($query, $params);

        // Delete bidder<->user relation
        $this->users->removeUserForBidder($userId, $id);

        // Delete bidder
        list ($query, $params) = $this->bidderSchema->delete(array ('id' => $id));
        $result = $this->db->execute($query, $params);

        if (!isset($result) || $result == 0)
            $app->halt(404);
    }

    private function _validate($data, $fields)
    {
        $publisher = array();
        foreach ($fields as $field) {
            if (!isset($data[$field]))
                return false;
        }

        return true;
    }

    // Get from body, the json representing the bidder & its filters
    // Usage list($bidder, $filters) = _getRequestParameters($app);
    private function _getRequestParameters($app)
    {
        $request = $app->request();
        $body = $request->getBody();
        $json = json_decode($body, true);

        $filters = array();
        if (isset($json['filters']))
        {
            $filters = $json['filters'];
            unset($json['filters']);
        }

        return array($json, $filters);
    }

    private function _addFilter($app, $filter, $bidderId)
    {
        $filter['bidder'] = $bidderId;

        if (isset($filter['value']))
            $filter['value'] = implode(';', $filter['value']);

        if (!$this->_validate($filter, bidders::$FILTER_FIELDS))
        {
            $this->db->execute('ROLLBACK');
            $app->halt(400);
        }

        list ($query, $params) = $this->filterSchema->set(RedMap\Schema::SET_INSERT, $filter);
        $result = $this->db->insert($query, $params);

        if (!isset($result))
        {
            $this->db->execute('ROLLBACK');
            $app->halt(409);
        }
    }
}

class Bidder
{
    public $id;
    public $name;
    public $bidUrl;
    public $rsaPubKey;
    public $sampling;
    public $filters;

    function __construct($row, $filters)
    {
        $this->id = (int) $row['id'];
        $this->name = $row['name'];
        $this->bidUrl = $row['bidUrl'];
        $this->rsaPubKey = $row['rsaPubKey'];
        $this->sampling = $row['sampling'];
        $this->filters = $filters;
    }
}

class BidderFilter
{
    public $type;
    public $bidder;
    public $mode;
    public $value;

    function __construct($row)
    {
        $this->type = $row['type'];
        $this->bidder = (int) $row['bidder'];
        $this->mode = $row['mode'];
        $this->value = explode(";", $row['value']);
    }
}

?>