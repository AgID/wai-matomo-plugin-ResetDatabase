# Matomo WAIResetDatabase Plugin

## Description

This plugin provides a console command that allows you to delete all tables and reimport them from the dump.

## Installation

Refer to [this Matomo FAQ](https://matomo.org/faq/plugins/faq_21/).

## Usage

Put the dump files in the sql folder.

You can specify more than one dump file.

An absolute or relative path can be specified.

Execute the command like:

`./console reset-database --dump="/path/to/dump.filename.first.sql" --dump="../path/to/dump.filename.second.sql"`