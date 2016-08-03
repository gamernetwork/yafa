from django.contrib import admin
from django.utils import timezone
from datetime import timedelta

from . import models

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


class AdvertAdmin(admin.ModelAdmin):
    list_display = ['name', 'active', 'start_date', 'end_date', 'tag_list']
    list_editable = ['active',]
    list_filter = ['active', 'tags', TimeFilter]

    def get_queryset(self, request):
        return super(AdvertAdmin, self).get_queryset(request).prefetch_related('tags')

    def tag_list(self, obj):
        return u", ".join(o.name for o in obj.tags.all())

admin.site.register(models.Site)
admin.site.register(models.Zone)
admin.site.register(models.Advert, AdvertAdmin)

