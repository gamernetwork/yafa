from django.contrib import admin
from django.forms import ModelForm
from django.utils import timezone
from datetime import timedelta

from . import models

# Filtering for Adverts

class TimeFilter(admin.SimpleListFilter):
    title = 'When'
    parameter_name = 'When'

    def lookups(self, request, model_admin):
        return (
            ('running', 'Running'),
            ('pending', 'Future campaigns'),
            ('soon', 'Starting soon'),
        )

    def queryset(self, request, queryset):
        if self.value() == 'running':
            return queryset.filter(
				start_date__lte=timezone.now(),
                end_date__gt=timezone.now()
			)
        if self.value() == 'pending':
            return queryset.filter(
				start_date__gt=timezone.now()
			)
        if self.value() == 'soon':
            return queryset.filter(
				start_date__gt=timezone.now(),
				start_date__lt=timezone.now()+timedelta(hours=24)
			)

# django-taggit-labels based form for selecting predefined tags

from taggit_labels.widgets import LabelWidget
from taggit.forms import TagField

class AdvertForm(ModelForm):
    tags = TagField(required=False, widget=LabelWidget(model=models.HierarchicalTag))

class AdvertAdmin(admin.ModelAdmin):
    list_display = ['name', 'site', 'active', 'start_date', 'end_date', 'tag_list']
    list_editable = ['active',]
    list_filter = ['site', 'active', 'tags', TimeFilter]

    form = AdvertForm

    save_as = True

    # tag prefetching to save on database queries
    def get_queryset(self, request):
        return super(AdvertAdmin, self).get_queryset(request).prefetch_related('tags')

    def tag_list(self, obj):
        return u", ".join(o.name for o in obj.tags.all())

admin.site.register(models.Advert, AdvertAdmin)

# register default admins for Site, Zone

admin.site.register(models.Site)
admin.site.register(models.Zone)

# register a basic hierarchical tag admin

from treebeard.admin import TreeAdmin
from treebeard.forms import movenodeform_factory
from models import HierarchicalTag

class TagTreeAdmin(TreeAdmin):
    form = movenodeform_factory(HierarchicalTag)

admin.site.register(HierarchicalTag, TagTreeAdmin)

# unregister taggit default admin for tags as we're using custom models
# and admin
from taggit.admin import Tag
admin.site.unregister(Tag)
