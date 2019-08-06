# Changelog
All notable changes to this project will be documented in this file.

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