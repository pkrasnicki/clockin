# About

A CLI program that helps to track working time and synchronizes it with Jira.

# Configuration

Create a config.yaml file within the `$HOME/.clockin` directory.
```yaml
jira:
    url: # jira cloud url
    user: # jira user
    token: # jira api token
    extractor:
        - # regex to find issue id in the time log's description
```

## Prerequisites

PHP 8.3^

To build a phar you need a [box](https://github.com/box-project/box) tool and a GNU Make.

## Build

`make`
