# Yafa Server

A simple POC ad server.

## Installation

Copy `example-settings_local.py` to `settings_local.py`. Configure with real MySQL database stuff.

Add these lines if you want to debug

```
DEBUG=True
```

Install the environment.

```
virtualenv env --no-site-packages
env/bin/pip install -r requirements.txt
```

Run the migrations.

```
env/bin/python manage.py migrate
```

Run the server

```
env/bin/python manage.py runserver
```


