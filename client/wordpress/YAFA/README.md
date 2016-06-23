
# YAFA Wordpress client plugin


Drop this folder into plugins and activate it.

The YAFA Admin is in the wordpress settings where you can modify the options and pull in the first ads for your site.

Drop ads into the same containers as DFP ads and call the get_ad(); method for your ad zone.

```
<div>
  <?php if(function_exists("get_yafa")){echo get_yafa()->get_ad("300x250");} ?>
  <div class="advert-container" data-dfp-id="VRFocus_Desktop_Homepage_MPU" data-dfp-sizes="300x250"></div>
</div>
```

To activate the ads during adblock put a try/catch statement around our normal DFP ads library and insert the dfp ad remove/YAFA setup code. This code may just be added to the plugin in the future using another method to detect adblock.

```
try
{
    $('.advert-container').getDFPads({
    ...
    });
catch(e)
{
    var dfp = document.querySelectorAll('[data-dfp-id]');
    for(var i = 0;i < dfp.length;i++)
    {
        dfp[i].parentNode.removeChild(dfp[i]);
    }
    var yafaList = document.querySelectorAll('[data-yafa-click]');
    for(var i = 0;i < yafaList.length;i++)
    {
        var yafa = yafaList[i];
        var link = document.createElement('a');
        link.setAttribute("href", yafa.getAttribute("data-yafa-click"));
        link.setAttribute("target", "_blank");
        var img = document.createElement('img');
        img.setAttribute("src", yafa.getAttribute("data-yafa-img"));
        link.appendChild(img);
        yafa.appendChild(link);
    }
}
```

