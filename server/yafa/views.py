from yafa.models import *

from django.http import JsonResponse, HttpResponse
from datetime import datetime

from django.core import serializers
from django.core.serializers.json import DjangoJSONEncoder

from hashids import Hashids


def all(request, site):
    ads = Advert.objects.filter(
        site__slug = site,
        end_date__gte = datetime.now()
    #).distinct(
    #    'zone'
    ).all()

    ad_manifest = [
        {
            ad.zone.slug : {
                'obfuscated_name': garble( ad.id ),
                'image': ad.image,
                'click': ad.click,
            }
        }
        for ad in ads
    ]
    #res = HttpResponse(serializers.serialize('json', ad_manifest), content_type='application/json')
    res = JsonResponse(ad_manifest, safe=False)
    return res

def garble(ad_id):
    hasher = Hashids(salt="Mmm lovely salty tang", min_length=8, alphabet="abcdefghijklmnopqrstuvwxyz")
    return hasher.encode(ad_id)
