# -*- coding: utf-8 -*-
# Generated by Django 1.9.6 on 2016-06-06 22:16
from __future__ import unicode_literals

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('yafa', '0001_initial'),
    ]

    operations = [
        migrations.RemoveField(
            model_name='site',
            name='name',
        ),
        migrations.RemoveField(
            model_name='zone',
            name='name',
        ),
        migrations.AddField(
            model_name='site',
            name='slug',
            field=models.SlugField(default='', max_length=250),
            preserve_default=False,
        ),
        migrations.AddField(
            model_name='zone',
            name='slug',
            field=models.SlugField(default='', max_length=250),
            preserve_default=False,
        ),
    ]
