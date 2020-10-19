# Changelog

All notable changes to `laravel-fileable` will be documented in this file

## v0.2.2 - 2020-10-19

- fix `fclose(): supplied resource is not a valid stream resource` exception

## v0.2.1 - 2020-10-13

- fix `\Astrotomic\Fileable\Concerns\Fileable::deleting()` listener to only return `null` or `false`

## v0.2.0 - 2020-10-13

-   fix `\Astrotomic\Fileable\Models\File` JSON response
-   add `\Astrotomic\Fileable\Contracts\File` interface
-   allow to customize file table and connection
-   fix custom model in `\Astrotomic\Fileable\Concerns\Fileable::files()` relationship
-   add `resource`/stream as supported type to `\Astrotomic\Fileable\FileAdder::add()`
-   add helper methods to `\Astrotomic\Fileable\Concerns\Fileable`
-   change `\Astrotomic\Fileable\Models\File::toResponse()` behavior

## v0.1.0 - 2020-10-02

-   initial release
