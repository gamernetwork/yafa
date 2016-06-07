# YAFA

Yet another adserver.

The most basic possible adserver, with a very fast and light
json API. Used as a POC for server -> server ad delivery
because adblock.

## Server

## Client

You site server side code will need to make a call to YAFA API:

```
http://server:port/v1/site/<sitename>
```

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

You will need adzone placeholders that look like this

```
<div class='ac'
    data-dfp-stuff...
    data-yafa-img='<image>'
    data-yafa-click='<click>'
    ...
    ></div>
```

### Local cache

You want to cache the calls to YAFA, because otherwise you will be
adding massive latency to your page renders, and the potential to
block if YAFA goes down.

#### Generic

Install a caching proxy in your network and go via that. Or roll your own DB-backed cache. NMFP, mate.

#### Wordpress

Install the plugin in wordpress/, it has a cron job which will
periodically query the API and cache the results in the local DB.






