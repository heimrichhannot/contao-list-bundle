# Changelog
All notable changes to this project will be documented in this file.

## [1.9.3] - 2019-10-24

#### Fixed
- jscroll offset rounding issue

## [1.9.2] - 2019-10-22

#### Fixed
- filter js integration

## [1.9.1] - 2019-10-16

#### Fixed
- list preselect option escaping issue

## [1.9.0] - 2019-10-16

#### Changed
- list preselect to also take into account the filter and the sorting of the associated list config
- moved getCurrentSorting to ListManager
- made more select fields in backend "chosen"s

## [1.8.4] - 2019-10-01

#### Fixed
- submission form

## [1.8.3] - 2019-09-30

#### Fixed
- select options for customized overview label

## [1.8.2] - 2019-09-27

#### Fixed
- exception handling

## [1.8.1] - 2019-09-27

#### Fixed
- customize jumpTo overview label

## [1.8.0] - 2019-09-27

#### Added
- optional jumpTo overview page

## [1.7.2] - 2019-09-24

#### Changed
- fixed form bug

## [1.7.1] - 2019-09-24

#### Changed
- renamed contact form config element type to submission form config element type

## [1.7.0] - 2019-09-24

#### Added
- contact form config element type

## [1.6.0] - 2019-09-13

#### Fixed
- inheritance issues

#### Added
- random placeholder session persistence
- pagination wrapper support for ajax pagination (.ajax-pagination can now have a wrapper div with the class ".pagination")

## [1.5.1] - 2019-09-13

#### Fixed
- reset the list config while parsing the items since it might have been reset in parsing process

## [1.5.0] - 2019-09-12

#### Added
- random placeholder mode for image config element type

## [1.4.0] - 2019-08-29

#### Fixed
- search listener issues

#### Added
- ajax pagination template

## [1.3.0] - 2019-08-22

#### Changed
- refactored js to es6 class including webpack support

## [1.2.0] - 2019-08-22

#### Added
- list config wizard to list module

## [1.1.1] - 2019-08-07

#### Fixed
- possible type error in ImageConfigElementType

## [1.1.0] - 2019-08-06

This release brings a new and easier way to register config element types. The old way (register the types in the config) is now deprecated and will be removed in the next major version. Please review the readme for introduction how to add config element types now. Upgrade old elements should be as easy as implement the new Interface, call the already existing method from the inherit method and register the class as service. 

#### Added 
- config element types can now be registered the "symfony way"
- config element type is now shown in the backend list

#### Changed
- updated the tl_list_config_element backend module header (filter and sort)
- refactored some methods

## [1.0.0] - 2019-07-30

#### Added 
* option to disable adding item details pages to the list of searchable pages

#### Changed
* updated list config search and filter settings for backend
