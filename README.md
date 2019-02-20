# Mosaico Templating Tool v1.1

This script is used in order to automate the customization of some language translations and style variables of a template. Let's take a look at an example.

Scenario: Start with one HTML mailing template, with the objective of translating it into 3 different languages while simultaneously changing some branding - a color - so it can be used on different sites with different themes.

You create
1. One template file (in some ways this is a meta-template, since it is used to generate Mosaico template files after substitution),
2. php configuration files with the language strings and
3. php configuration files with the style settings.
Then 4. you run the script.

Output: versions of the template file for each specified combination of language and style.

A clear example can be seen in the configuration files (languages and styles) in the '/config' directory, and a template sample in the '/templates' directory.

Let's see how it works:
___
## 1. Initial configuration

First of all, if there's not a '/vendor' directory in the project folder you should run `$ composer require twig/twig` from the command line in the project folder. Make sure that a file called 'autoload.php' exists inside the '/vendor' directory.
___
## 2. Creating the template structure

If you have successfully cloned this repository in the project folder, there will be two folders beside the /vendor folder: /templates and /config.

Inside /templates, there will be a file named 'sample.tpl', which is a short sample of showing how to use twig variables. All the twig expressions have this appearance `{{ example_variable }}`. These variables will be replaced with the chosen strings when the file gets evaluated. For example, the following structure:
```
    <div style="color: white; background-color: {{ brand_color }}">
        <p> Hi {contact.display_name}!</>
        <p> {{ text }} </p>
        <p> Sincerely, {{ name }} </p>
    </div>
```
depending on preferences, may create as one of its outputs (for language en and color blue) something similar to:
```
    <div style="color: white; background-color: #00cccc">
        <p> Hi {contact.display_name}!</>
        <p> This is a sample showing how this works. </p>
        <p> Sincerely, John Doe </p>
    </div>
```

NB: In real life instead of our sample, the 'Dear' and 'Sincerely' would also be translated strings in the language files. In the sample, the name of person signing the letter is 'John Doe' in English, 'Juan Doe' in Spanish and 'Joan Doe' in Catalan. All the templates produced will have a CiviCRM token for the display name of the addressee. CiviCRM will replace this {contact.display_name} token as it processes the output template for each recipient of a mailing.

___
## 3. Creating the configuration files

For each variable in your created template file, there must be defined strings to replace it. For convenience and clarity, variables that have different values based on the language are put in files under a /langs subdirectory, while variables that are not language dependent are put in files under a /vars subdirectory. Running the script will substitute the strings as appropriate into the template, creating various templates suitable for use in Mosaico. We recommend providing substitution values for a Twig variable either in the /config/langs files or in the /config/vars files, but not both.

These strings are put inside the '/config' folder. Under the config folder there must be a subfolder with the same name as the template file (eg: If the template file is '/templates/template1.tpl', then inside the '/config' folder there has to be a directory called 'template1'). For each template configuration folder, there must be 2 different subdirectories inside the matching config folder: 'langs' and 'vars'.

* All the language translation strings will be inside the 'langs' folder, written in one file for each language (eg: If you want an English and Spanish translation of the template, there must be the 'en.php' and 'es.php' files inside the 'langs' folder). For each variable defined inside the template file there must be a string to replace it in each of the language files. If not, the variable will show an empty space in the final result.

* All the style customization strings will be inside the 'vars' folder, written in one file for each result of your preference. For example, if you want 3 different results based on 3 different colors, there must be three files inside the 'vars' folder, perhaps 'blue.php', 'green.php' and 'red.php'. Again, for each variable defined inside the template file that is being replaced using values provided by /vars files, there must be a string to replace it in each vars file or an empty space will show in the final result.

* Note that it is possible to specify all or a subset of languages be rendered for a specific value of a vars variable. Inside the main array in the /vars files, there is a subarray called 'langs'. If it's empty, the script will create all the different results according to the languages inside the '/langs' folder by default. You can choose fewer languages if desired by adding items at the array, as follows (this excludes 'ca' language from being rendered for this option):
```
...
    'langs' => array(
        'es','en'
    ),
...
```
The items inside the array must exist in the /'langs' directory, if not, it will not create it. (In other words, if you add the item 'en' to the array, there must be a file inside '/config/langs' called 'en.php')

The sample provided with the tool displays the following structure as described above:

```
config
  └── template-sample
     ├── langs
     │   ├── ca.php
     │   ├── en.php
     │   └── es.php
     └── vars
         ├── blue.php
         ├── green.php
         └── red.php
templates
  └── template-sample.tpl

```
___
## 4. Running the script

Once the template file and the configuration files are correctly created, just run the script (`$ php generateTpl.php`), and it will create the /dist directory by itself, containing the final customized HTML files, similar to this:
```
dist
  └── sample-blue-en
  │    └── template-sample-blue-en.html
  ├── sample-green-ca
  │    └── template-sample-green-ca.html
  ├── sample-green-en
  │    └── template-sample-green-en.html
  ├── sample-green-es
  │    └── template-sample-green-es.html
  ├── sample-red-ca
  │    └── template-sample-red-ca.html
  └── sample-red-en
       └── template-sample-red-en.html
```

Feel free to run the tool on the sample template in order to see the output. The sample configuration files illustrate how to use twig's syntax and how to format the configuration files.

### HTMML compatibility

This script can be used to generate [HTMML](https://github.com/voidlabs/htmml) templates.

You need to define twig variables inside HTMML templates with `[[` `]]` delimiters.

Run the script with the `--htmml` option `$ php generateTpl.php --htmml`.

___
## 5. Troubleshooting

The script outputs not only every made step, but and also every error found in the directory configuration.

1. If there are problems with the 'vendor' folder, make sure that the twig templates support is succesfully installed. If necessary run `$ composer require twig/twig` in the command line to install it. It will create a '/vendor' folder and the 'autoload.php' within it.

2. If the issue is related to the configuration files or folders, make sure that you have followed the instructions above. Be especially careful with the names of the templates and the config folder names.
    > Section 3: Creating the configuration files:
    >  "... If the template file is called 'template1.tpl', inside the '/config' folder must be a directory called 'template1'..."

    > Section 3: Creating the configuration files:
    >  "... If you add the item 'en' to the array, there must be a file inside '/config/langs' called 'en.php')

Anyways, the script will output some hints if something is not working properly.

## Changelog

### 1.1

- Add HTMML compatibility
- Switch configuration and language order on generated templates filename
___
## Credits

Mario Recamales [@MarioClot](https://github.com/MarioClot)

Francesc Bassas i Bullich [@francescbassas](https://github.com/francescbassas)

### Contributors
Joe Murray [@JoeMurray](https://github.com/JoeMurray)
