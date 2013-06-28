piMediaTesterPlugin
===================

Diem plugin to test if there are no text references to nonexisting medias

## Plugin genesis

[Diem](https://github.com/diem-project/diem) is an open source CMF/CMS for [Symfony 1.4](https://github.com/symfony/symfony1). One of it's core functionalities are medias: drag'n'droppable multimedia interface, that allows adding links to files (or thumbnails at will) to any [MarkItUp](https://github.com/markitup) field (markdown/markdown extra enabled textarea) or Diem [widget](http://diem-project.org/diem-5-1/doc/en/reference-book/widgets)

The only problem with medias is that you never know if a media referred by ID still exists.
Medias are not protected against deleting if ever referred to (eg. constraints) on database level.
Diem provides no way to prevent deleting on apllication-level either.

This plugin is an attept to patch default Diem's behavior (namly: ignoring nonexisting references)

## Usage

Plugin adds new page to Media admin interface. On this page you'll find a report containing all found references to broken medias.

### Reasons

Media reference can be broken for 3 reasons:
* there is no media record for given ID (which is most common reason )
* there is a record corresponding to given ID but file is not present (can happen only if something unexpected happens to media files)
* there is no file specified in existing media record (strictly theoretical reason)

Reason #1 is not related to any kind of error in system - it can happen if yopu delete media record.
It's just because there's no way to get notified if this media is used as reference.

## How it works

Tester service checks *standard diem widgets* referring media: ([Content-Text](http://diem-project.org/diem-5-1/doc/en/reference-book/widgets#built-in-widgets:content:text), [Content-Image](http://diem-project.org/diem-5-1/doc/en/reference-book/widgets#built-in-widgets:content:image)) as well as *all project models* using 

    extra: markdown 

in table column definition in schema.yml

## TODO

* add a warning before deleting media that is referred in any known part of system
* convert it to task (it's already designed to be context-agnostic since logic is written as a service, not module)