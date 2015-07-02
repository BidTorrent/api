# Track.bidtorrent.io


## Impressions

This endpoint tracks all impressions of BidTorrent for a given publisher

	{host}/imp?auction=XXX&b1=bidder1&d[{bidderId1}]=signedBidInfo1&d[{bidderId2}]=signedBidInfo2&

- Each `signedBidInfo1` is in the form "auctionid-price"
- Price is a float in EUR for one thousand impression
- Then the tuple is signed with the private key of the bidder

## Example

- http://track.bidtorrent.dev/src/imp.php?a=auction1&p=10&f=0.05&d[1]=0.123-KVco5%2FSeEdbVMHep3a4YMftdd4jOLvdgqhizZKIk4VoGx9ozSDYlirTXa3FTbsytMeyPUL6HZuRwGVVewA4qo7HGe3%2BQGHYRgUb8v%2F2q%2FH26LS%2B8n9dotLc0QVGgIlaNuZG3G08E%2FC2w0MLsALEgqnFpovzhe3nzOapKAZc5XUk%3D

- key generation can be done at http://travistidwell.com/jsencrypt/demo/