# Contao List Bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-list-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-list-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-list-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-list-bundle)
[![Build Status](https://travis-ci.org/heimrichhannot/contao-list-bundle.svg?branch=master)](https://travis-ci.org/heimrichhannot/contao-list-bundle)
[![Coverage Status](https://coveralls.io/repos/github/heimrichhannot/contao-list-bundle/badge.svg?branch=master)](https://coveralls.io/github/heimrichhannot/contao-list-bundle?branch=master)

This bundle offers a generic list module to use with arbitrary contao entities containing standard list handling like pagination, sorting, and filtering.

## Features
* generic list module to use with arbitrary contao entities
* support all form of standard list handling like pagination, sorting, filtering
* works together with filter bundle
* inheritable list configurations
* template are build in twig
* [Encore Bundle](https://github.com/heimrichhannot/contao-encore-bundle) support

## Usage

### Install

1. Install via composer: `composer require heimrichhannot/contao-list-bundle` or contao manager
1. Update your database

Recommendations:
* use this bundle together with [Reader Bundle](https://github.com/heimrichhannot/contao-reader-bundle).
* use [Encore Bundle](https://github.com/heimrichhannot/contao-encore-bundle) for managing your frontend assets

### Setup
1. Setup a filter (see [Filter Bundle](https://github.com/heimrichhannot/contao-filter-bundle) setup)
1. Create a list config (System -> List config)
    * To add list elements like images, videos, slider etc, add list config elements (see Concepts -> List config elements for explanation)
1. Create a list frontend module and output it, where you like it

## Concepts

### Inheritable list configuration

Since list configuration can be lots of data sometimes we decided to outsource it into a dedicated DCA entity.
These entities can be assigned to one or even multiple list modules in a reusable way.

In addition it's possible to create list configs that inherit from other list configs.
Hence overriding a single option while keeping everything else is possible.

### List config elements

Every list config can have one or more list config elements. These are designed to specify things that can occur multiple times (e.g. because there are many fields of one type).

Currently available list config element types:

Type  | Description
------|------------
image | Configure the output of one or more image fields separately (image size, placeholder handling, ...)

## Documentation

[Developer documentation](docs/developers.md)