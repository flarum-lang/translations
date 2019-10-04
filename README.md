# Flarum Lang

The Flarum translation collaborative.

- We store Flarum core translations and third party translations separately.
- We automatically pull the latest version of an extension to read and sync translations for.
- We use lokalise for:
    - [core translations](https://lokalise.com/public/722660935d41917e602af4.06892971/).
    - [third party translations](https://lokalise.com/public/905741775d41e764bd0b00.56796766/).

## Adding extensions

Extensions are stored inside the `extensions` directory. In order to
add new extensions you will need to do a PR against the repository
with a new file inside that directory. The name of the file has to be
equal to the name Flarum uses for her extensions:
 
 - (flarum-ext- and flarum-) are stripped
 - the slash after the vendor is replaced with a dash
 - filetype `yml` is used
 - `hyn/flarum-ext-default-group` becomes `hyn-default-group.yml`
 
 The yml has to contain the following information:
 
 ```yaml
 name: Split
 repository: git@github.com:friendsofflarum/split.git
 directory: locale
 matches: en.yml
 lang: en
```

- Name is a human readable name for the extension.
- Repository is the git repository for the extension using the git/ssh protocol.
- Directory indicates where in the repository the localisation files are contained.
- Matches tells us how to find files, this can be a regular expression, one file or using wildcards.
- Lang specifies what language these files are in. Don't upload different languages, use English if available! 
 
## Translators

In order to guarantee the best translations to flew into our language packs, we've decided to give some structure to this team:

- Flarum core team members are responsible to keep the translation automation going.
- Translation reviewers approve new and changed translations.
- Translators are free to suggest new and alternative translations.

Only reviewed translation keys will be used inside our translations.
