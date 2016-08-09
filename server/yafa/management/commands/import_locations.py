from django.core.management.base import BaseCommand, CommandError
from yafa.models import *
import sys
import csv

class Command(BaseCommand):
    help = 'Import locations from CSV piped into stdin'

    def handle(self, *args, **options):
        reader = csv.reader(sys.stdin, delimiter=',', quotechar='"')
        for row in reader:
            cont = row[0] # continent
            cc = row[1] # country code
            name = row[2] # country name
            continent = HierarchicalTag.objects.get(slug=cont)
            try:
                country = HierarchicalTag.objects.get(slug=cc)
                country.name = name
                country.save()
            except HierarchicalTag.DoesNotExist:
                country = continent.add_child(slug=cc, name=name)
                continent.save()
                country.save()
