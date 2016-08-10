# YAFA

Yet another adserver.

The most basic possible adserver, with a super simple
web API. Used as a POC for server -> server ad delivery
because adblock.

## Server

See [server/README.md].

## Client

You site server side code will need to make a call to YAFA API:

```
http://server:port/v1/site/<sitename>?[tags=<geo-or-context>]&[limit=<max-ads-per-zone>]
```

  - `tags` default to none, which means no filtering. YAFA has hierarchical
    tagging, for instance, if you set up continent->country tagging, a request
    for a country with return ads tagged with the continent.
  - `max-ads-per-zone` defaults to 1. When multiple ads are set for a zone,
    they will be randomly sampled.

And will return an object in this form:

```
{
  '<zone>' : {
    'obfuscated_name' : '<something garbled>',
    'image' : '<url of image>',
    'click' : '<click url>'
  },
  '<zone>' : {
    'obfuscated_name' : '<something garbled>',
    'image' : '<url of image>',
    'click' : '<click url>'
  },
  ...
}
```

You could use the API output to create adzone placeholders that look like this

```
<div class='ac'
    data-dfp-stuff...
    data-yafa-img='<image>'
    data-yafa-click='<click>'
    ...
    ></div>
```

Then use this info an element block dodging manner, such as applying the image
as a background image on a site critical element, and synthesising clicks using
mouse event capture.

### Local cache

You want to cache the calls to YAFA, because otherwise you will be
adding massive latency to your page renders, and the potential to
block if YAFA goes down.

#### Generic

Install a caching proxy in your network and go via that. Or roll your own DB-backed cache. NMFP, mate.

#### Wordpress

See [github.com/gamernetwork/wp-yafa] for a handy plugin that does all this for
you.




