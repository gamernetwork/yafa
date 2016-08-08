from yafa.models import *

from django.http import JsonResponse, HttpResponse
from django.utils import timezone

from django.core import serializers
from django.core.serializers.json import DjangoJSONEncoder

from hashids import Hashids

from random import choice, sample

from django.conf import settings

def all(request, site):
    ads = Advert.objects.filter(
        site__slug = site,
        end_date__gte = timezone.now(),
        active=True
    )
    
    tag_slugs = filter(
        lambda x: x != '',
        request.GET.get('tags', default='').split(',')
    )

    if len(tag_slugs) > 0:
        # get ancestors of all selected tags
        # e.g. if I ask for 'UK' ads, I want all ads targeted to 'UK', 'Europe' and 'Everywhere'
        cloud = set()
        for tag_slug in tag_slugs:
            try:
                tag = HierarchicalTag.objects.get(slug=tag_slug)
                cloud.add(tag)
                cloud.update(tag.get_ancestors())
            except HierarchicalTag.DoesNotExist, e:
                if settings.DEBUG:
                    raise e

        ads = ads.filter( tags__in = cloud )

    ad_manifest = {}
    for ad in ads:
        if ad.zone.slug not in ad_manifest:
            ad_manifest[ad.zone.slug] = []

        ad_manifest[ad.zone.slug].append( {
            'obfuscated_name': garble( ad.id ),
            'image': ad.image,
            'click': ad.click,
        } )

    # if there are multiple ads per zone, pick <limit> at random
    # TODO make this weighted or priority driven
    sample_size = int(request.GET.get('limit', '1'))
    for zone in ad_manifest.keys():
        ad_manifest[zone] = sample(
            ad_manifest[zone],
            min(sample_size, len(ad_manifest[zone])) # sample > population will result in error
        )

    #res = HttpResponse(serializers.serialize('json', ad_manifest), content_type='application/json')
    res = JsonResponse(ad_manifest, safe=False)
    return res

def garble(ad_id):
    hasher = Hashids(salt="Mmm lovely salty tang", min_length=8, alphabet="abcdefghijklmnopqrstuvwxyz")
    return hasher.encode(ad_id)
