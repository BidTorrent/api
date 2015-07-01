# Track.bidtorrent.io


## Impressions

This endpoint tracks all impressions of BidTorrent for a given publisher

	{host}/imp?auction=XXX&b1=bidder1&d[{bidderId1}]=signedBidInfo1&d[{bidderId2}]=signedBidInfo2&

- Each `signedBidInfo1` is in the form "auctionid-price"
- Price is a float in EUR for one thousand impression
- Then the tuple is signed with the private key of the bidder

## Example

- http://track.bidtorrent.dev/src/imp.php?d[1]=wxcsdwvwd4wd1wvwd3vw-0.123456-1-auen64Wq%2FI%2FzfWyUvHxY5024eic1AO6I5tR6qGZ4RUlzlZZbSMcMyxb44Wv6PfHwrQzg3xR%2FB55ypYypBX3sm9002nIGH%2B6rCpqx8qXAmKISgF%2FseYmhg55P071Jo8ekTB%2BhTKZz67Xl4xoRlRReqwQSrzRU5jHc12swUAf7c9I%3D