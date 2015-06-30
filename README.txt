# Track.bidtorrent.io


## Impression

This endpoint tracks all impressions of BidTorrent for a given publisher

	{host}/imp?auction=XXX&bids={bidderid1}-{signedBidInfo1};{bidderid2}-{signedBidInfo2} ...

- Each `signedBidInfo1` is in the form "auctionid-price"
- Price is a float in EUR for one thousand impression
- Then the tuple is signed with the private key of the bidder

## Other end point

TODO