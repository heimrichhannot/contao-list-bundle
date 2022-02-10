# Changelog

All notable changes to this project will be documented in this file.

## [1.48.2] - 2022-02-10

- Fixed: Twig type hint for symfony 5+
- Fixed: querybuilder parameter colons for symfony 5+
- Fixed: transChoice() call for symfony 5+
- Removed: call to Utf8 functions

## [1.48.1] - 2022-02-09

- Fixed: config for symfony 5+
- Removed: twig extension service definitions

## [1.48.0] - 2022-02-08

- Added: support for contao 4.13
- Changed: minimum contao version is now 4.9
- Changed: supported symfony versions to `^4.4||^5.4`
- Changed: replaced `twig/extensions` by `twig/extra-bundle`

## [1.47.3] - 2022-02-07

- Fixed: ajax pagination with masonry

## [1.47.2] - 2022-02-03

- Added: support for custom relationTable for tag-based `RelateConfigElementType`

## [1.47.1] - 2022-01-07
- Fixed: service registration for symfony 4+

## [1.47.0] - 2022-01-06
- Added: new option to list frontend module for showing reader module instead of list module. Needs reader bundle installed and reader config item retrival mode is set to auto_item

## [1.46.0] - 2021-11-30
- Added: modal load error js event

## [1.45.0] - 2021-11-25
- Added: modal link clicked js event

## [1.44.2] - 2021-11-25
- Changed: enhanced modal javascript evaluation code

## [1.44.1] - 2021-11-25
- Fixed: response javascript not evaluated on modal open

## [1.44.0] - 2021-11-24
- Added: modal show javascript event

## [1.43.0] - 2021-11-11

- Added: stopwatches for symfony debug bar (huh.list.*)
- Changed: small performance enhancement
- Fixed: replaced a template util leftover with twig support bundle
- Fixed: performance optimization for counting items

## [1.42.0] - 2021-11-01
- Added: ListBeforeApplyConfigElementsEvent
- Fixed: formatted return values of config element types not correctly handled

## [1.41.0] - 2021-10-25
- Added: paginationData to DefaultList
- Added: RandomPagination::getTemplate()

## [1.40.3] - 2021-09-03

- Fixed: wrong scope in ajax pagination (case: multiple lists on one page)

## [1.40.2] - 2021-09-03

- Fixed: searchable pages generation for empty `$intRoot` parameter

## [1.40.1] - 2021-09-01

- Fixed: php8 support
- Fixed: quotes in js

## [1.40.0] - 2021-08-30

- Added: php8 support

## [1.39.1] - 2021-08-17

- Fixed: BS5 modal support for javascript

## [1.39.0] - 2021-08-17

- Added: BS5 modal support for javascript

## [1.38.0] - 2021-08-17

- Added: BS5 modal templates

## [1.37.1] - 2021-07-15

- fixed support for multilingual initial filter field values for list preselect

## [1.37.0] - 2021-07-14

- added support for multilingual initial filter field values for list preselect

## [1.36.0] - 2021-07-01

- removed jscroll library(#9)

## [1.35.1] - 2021-06-29

- added null check in preselect

## [1.35.0] - 2021-06-29

- added Polish translations

## [1.34.0] - 2021-06-23

- added support for multilingual initial filter field values
- fixed multilingual jump to pages (not entirely dependent on dc multilingual)

## [1.33.8] - 2021-06-18

- fixed optional support
  for [heimrichhannot/contao-multilingual-fields-bundle](https://github.com/heimrichhannot/contao-multilingual-fields-bundle)

## [1.33.7] - 2021-06-17

- fixed image size callback

## [1.33.6] - 2021-06-08

- fixes for contao 4.9

## [1.33.5] - 2021-06-08

- fixed typed properties

## [1.33.4] - 2021-06-08

- fixed typed properties

## [1.33.3] - 2021-05-20

- fixed close button in `submission_form_modal_bootstrap5.html.twig`

## [1.33.2] - 2021-05-19

- replace all service aliased with classed in tl_list_config_element
- extend submission config element with template choice and dynamic field mapping

## [1.33.1] - 2021-05-10

- allow twig-support-bundle ^1.0

## [1.33.0] - 2021-04-28

- Add listContextVariables field ([#6])
- Fix inserttags on async reload ([#7])

## [1.32.0] - 2021-04-19

- added optional support
  for [heimrichhannot/contao-multilingual-fields-bundle](https://github.com/heimrichhannot/contao-multilingual-fields-bundle)

## [1.31.3] - 2021-02-08

- made ajax list optional for async filter (a filter can also be used without a list)

## [1.31.2] - 2021-02-04

- fixed tl_list_config not found when migrating

## [1.31.1] - 2021-02-03

- fixed types in attributes (incompatible in some php versions)
- fixed sql issue (ambiguous pid)

## [1.31.0] - 2021-02-03

- added a new visual presentation for nested list configurations which should be way easier to read

## [1.30.7] - 2021-02-02

- fixed missing `addDetails` in `list_table_default.html.twig`

## [1.30.6] - 2021-02-02

- fixed autocompletion issues in make command
- fixed sorting issue in table template

## [1.30.5] - 2021-01-25

- fixed issue concerning modal handling and ajax pagination

## [1.30.4] - 2020-12-15

- fixed redirect issue in `TagsConfigElementType`

## [1.30.3] - 2020-12-15

- fixed ctable definition in tl_list_config was not an array

## [1.30.2] - 2020-12-11

- changed: get fields for select definition of queryBuilder in `DefaultList` from event

## [1.30.1] - 2020-12-11

- fix tl_list_config_element.templateVariable not mandatory for instances of ConfigElementTypeInterface ([#4])

## [1.30.0] - 2020-12-11

- Add live region for asynchronous list pagination  ([#5])

## [1.29.0] - 2020-12-10

- added more context to pagination template

## [1.28.1] - 2020-10-29

- added missing dependency

## [1.28.0] - 2020-10-21

- added new symfony command `huh-list:make` for easier creating list modules containing a list config and a filter

## [1.27.1] - 2020-10-06

- fixed list preselect

## [1.27.0] - 2020-10-01

- added multilingual details pages regardless of whether DC_Multilingual is installed (since this isn't the point)

## [1.26.0] - 2020-09-30

- enhanced the README.md (now contains info about items and image config elements)

## [1.25.0] - 2020-09-22

- moved to twig support bundle for templates
- updated callback services

## [1.24.3] - 2020-09-16

- fixed missing space at list modal templates

## [1.24.2] - 2020-08-27

- fixed missing template comments for list templates

## [1.24.1] - 2020-08-26

- fixed template var issue

## [1.24.0] - 2020-07-31

- added heimrichhannot/contao-config-element-type-bundle dependency
- ConfigElementTypeInterface is now the default way to implement ConfigElementTypes
- deprecated ListConfigElementTypeInterface and ListConfigElementData
- added template comments in dev mode
- fixed an issue in ImageConfigElementType

## [1.23.2] - 2020-07-31

- fixed fields for video config element
- added customization of templateContainerVariable for config elements

## [1.23.1] - 2020-07-28

- removed php 7.4 typing

## [1.23.0] - 2020-07-27

- added `VideoConfigElementType`

## [1.22.0] - 2020-07-16

- added bootstrap row list template

## [1.21.0] - 2020-06-30

- added option to open images processed by `ImageConfigElement`s in a lightbox

## [1.20.4] - 2020-06-23

- fixed bug concerning dc_multilingual and frontend preview

## [1.20.3] - 2020-06-18

- fixed typo in `SearchListener`

## [1.20.2] - 2020-06-02

- fixed negative limit issue

## [1.20.1] - 2020-05-27

- restored the list config after every list config element rendering
- skipped dc for default template

## [1.20.0] - 2020-05-25

- added category mode for `RelatedConfigElementType`

## [1.19.3] - 2020-05-25

- fixed `TagsConfigElementType`

## [1.19.2] - 2020-05-20

- fixed `TagsConfigElementType`

## [1.19.1] - 2020-05-20

- fixed `RelatedConfigElementType`

## [1.19.0] - 2020-05-19

- added new list config elements: `RelatedConfigElementType`, `TagsConfigElementType`

## [1.18.0] - 2020-05-19

- added the data container to the module's generated css class

## [1.17.3] - 2020-05-15

- fixed image config element for svg files

## [1.17.2] - 2020-05-08

- increased row size of `sortingMode` to 32

## [1.17.1] - 2020-05-07

- fixed type hint issue

## [1.17.0] - 2020-05-06

- added new implementation for opening list items in modals (and deprecated the old modal module-based one)

## [1.16.3] - 2020-04-22

- added `decodeEntities` to `sortingText`

## [1.16.2] - 2020-04-22

- increased `sortingText` size to 255

## [1.16.1] - 2020-04-20

- fixed dca issues for contao 4.9

## [1.16.0] - 2020-04-20

- fixed default value for addOverview

## [1.15.2] - 2020-04-20

- fixed dca labels for contao 4.9

## [1.15.1] - 2020-04-16

- fixed some non-public service

## [1.15.0] - 2020-04-09

- added `huh.list.list_update_complete` javascript event
- improved documentation

## [1.14.4] - 2020-04-06

- fixed some non-public service

## [1.14.3] - 2020-04-02

- fixed localizations

## [1.14.2] - 2020-03-31

- added title attribute to links in list_item_default template

## [1.14.1] - 2020-03-13

- fixed load_callback to support callables

## [1.14.0] - 2020-02-24

- enhanced events

## [1.13.0] - 2020-01-23

- added field dependent placeholder images

## [1.12.2] - 2019-12-17

- fixed exception when multilingual jump to page not set

## [1.12.1] - 2019-12-16

- fixed search index issues

## [1.12.0] - 2019-12-11

- added support for multilingual jump to pages in sitemap generation

## [1.11.0] - 2019-12-11

- changed default value for doNotIndex and doNotSearch to 1

## [1.10.0] - 2019-12-09

- added shortcut for list configs
- added support for multilingual details and overview pages

## [1.9.11] - 2019-12-05

- fixed group by issue for multilingual

## [1.9.10] - 2019-12-04

- fixed group by issue for multilingual

## [1.9.9] - 2019-12-04

- fixed group by issue for multilingual

## [1.9.8] - 2019-12-02

- fixed dc_multilingual related bug with order fields

## [1.9.7] - 2019-11-28

- fixed dc_multilingual related bug

## [1.9.6] - 2019-11-21

- fixed multifileupload related bug

## [1.9.5] - 2019-11-12

- fixed dc_multilingual related bug

## [1.9.4] - 2019-11-04

#### Fixed

- some methods not working on Item properties (like empty) (implemented _isset method)

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
- pagination wrapper support for ajax pagination (.ajax-pagination can now have a wrapper div with the class "
  .pagination")

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

This release brings a new and easier way to register config element types. The old way (register the types in the
config) is now deprecated and will be removed in the next major version. Please review the readme for introduction how
to add config element types now. Upgrade old elements should be as easy as implement the new Interface, call the already
existing method from the inherit method and register the class as service.

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

[#7]: https://github.com/heimrichhannot/contao-list-bundle/pull/7

[#6]: https://github.com/heimrichhannot/contao-list-bundle/pull/6

[#5]: https://github.com/heimrichhannot/contao-list-bundle/pull/5

[#4]: https://github.com/heimrichhannot/contao-list-bundle/issues/4
