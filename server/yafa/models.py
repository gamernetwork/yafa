from django.db import models

from taggit.managers import TaggableManager

class Site(models.Model):
    slug = models.SlugField(max_length=250)
    url = models.CharField(max_length=2083)

    def __str__(self):
        return self.slug

class Zone(models.Model):
    slug = models.SlugField(max_length=250)

    def __str__(self):
        return self.slug

class Advert(models.Model):
    name = models.CharField(max_length=250)
    image = models.CharField(max_length=2083)
    click = models.CharField(max_length=2083)
    start_date = models.DateTimeField()
    end_date = models.DateTimeField()

    active = models.BooleanField(default=True)

    site = models.ForeignKey(Site)
    zone = models.ForeignKey(Zone)

    tags = TaggableManager(blank=True)

    def __str__(self):
        return self.name

