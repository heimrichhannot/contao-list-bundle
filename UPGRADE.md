# Upgrade

Breaking changes

## Beta to 1.0

### Constants

`\HeimrichHannot\ListBundle\Backend\Module::MODULE_LIST` changes to `\HeimrichHannot\ListBundle\Module\ModuleList::TYPE`

### Services

Old Name                 | New Name 
-------------------------|---------
huh.list.backend.module  | huh.list.datacontainer.module
huh.list.backend.content | huh.list.datacontainer.content