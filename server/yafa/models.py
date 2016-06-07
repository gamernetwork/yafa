from django.db import models

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

    site = models.ForeignKey(Site)
    zone = models.ForeignKey(Zone)

    def __str__(self):
        return self.name

