from yafa.models import *

from django.http import JsonResponse, HttpResponse
from django.utils import timezone

from django.core import serializers
from django.core.serializers.json import DjangoJSONEncoder

from hashids import Hashids


def all(request, site):
    ads = Advert.objects.filter(
        site__slug = site,
        end_date__gte = timezone.now()
    ).all()

    zones = Zone.objects.all()

    ad_manifest = {}
    for ad in ads:
        # if there is more than one ad per zone, this will
        # just pick whichever comes out last
        # TODO: distribute?
        ad_manifest[ad.zone.slug] = {
            'obfuscated_name': garble( ad.id ),
            'image': ad.image,
            'click': ad.click,
        }

    #res = HttpResponse(serializers.serialize('json', ad_manifest), content_type='application/json')
    res = JsonResponse(ad_manifest, safe=False)
    return res

def garble(ad_id):
    hasher = Hashids(salt="Mmm lovely salty tang", min_length=8, alphabet="abcdefghijklmnopqrstuvwxyz")
    return hasher.encode(ad_id)
