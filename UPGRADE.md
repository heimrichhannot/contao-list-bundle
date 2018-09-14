# Upgrade

Breaking changes

## Beta to 1.0

### Constants

#### Moved

Old                                                           | New
--------------------------------------------------------------|----
`\HeimrichHannot\ListBundle\Backend\Module::MODULE_LIST`      | `\HeimrichHannot\ListBundle\Module\ModuleList::TYPE`
`\HeimrichHannot\ListBundle\Backend\ListBundle::ACTION_SHARE` | `\HeimrichHannot\ListBundle\HeimrichHannotContaoListBundle::ACTION_SHARE`

### Services

Old Name                 | New Name 
-------------------------|---------
huh.list.backend.module  | huh.list.datacontainer.module
huh.list.backend.content | huh.list.datacontainer.content