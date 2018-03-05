# Mosaico Templating Tool

This script is used in order to automate the customization of some language translations and style variables of a template. Let's take a look at an example: 
In case of having a HTML mailing template and wanting to translate it into 3 different languages plus change some colors for each result file. With this script we just need to create 1 template file, the php configuration files with the language strings and the style settings and run the script. A clear example can be seen in the configuration files (languages and styles) in the '/config' directory, and a template sample in the '/templates' directory.

Let's see how it works:
___
## 1. Initial configuration

First of all, if there's not a '/vendor' directory in the project folder you should run `$ composer require twig/twig` from the command line in the project folder. Make sure that a file called 'autoload.php' exists inside the '/vendor' directory.
___
## 2. Creating the template structure

If you have successfully cloned this repository in the project folder, look for a folder called 'templates'. If not, you can just create it. Inside, there must be a file named 'sample.tpl', which is a short sample of showing how to use twig variables. All the twig expressions have this appearance `{{ example_variable }}`. This variables will be replaced with the chosen strings when the file gets evaluated, like in the following structure: 
```
    <div style="color: white; background-color: {{ brand_color }}">
        <h1>Hi {{ name }}!</h1>
        <p> {{ text }} </p>
    </div>
```
Depending on the preferences, may create an output similar to:
```
    <div style="color: white; background-color: #00cccc">
        <h1>Hi John Doe!</h1>
        <p> This is a sample showing how this works </p>
    </div>
```
___
## 3. Creating the configuration files

Once the template file is created, there must be the defined strings to replace each variable of the template. These strings must be inside the '/config' folder. Inside there must be a folder with the same name of the template file (fe: If the template file is called 'template1.tpl', inside the '/config' folder must be a directory called 'template1'). For each template configuration folder, there must be 2 different directories inside, called 'langs' and 'vars': 
* All the language translation strings will be inside the 'langs' folder, written in one file for each language (fe: If you want an English and Spanish translation of the template, there must be the 'en.php' and 'es.php' files inside the 'langs' folder). For each variable defined inside the template file there must be a string to replace it, if not the variable will show an empty space in the final result.

* All the style customization strings will be inside the 'vars' folder, written in one file for each result of your preference (fe: If you want 3 different results with 3 different colors, there must be the 'color1.php', 'color2.php' and the 'color3.php' inside the 'vars' folder). For each variable defined inside the template file there must be a string to replace it, if not the variable will show an empty space in the final result. Inside this file, there's and option to choose which are the desired languages to translate the template. Inside the main array, there is a subarray called 'langs'. If it's empty, the script will create all the different results according to the languages inside the '/langs' folder by default. You can choose the languages of your preference by adding items at the array, this way: 
```
...
    'langs' => array(
        'es','en'
    ),
...
```
The items inside the array must exists in the /'langs' directory, if not, it will not create it. (fe: If you add the item 'en' to the array, there must be a file inside '/config/langs' called 'en.php')
According to the notes above, there must be a directory structure similar to this:

```
config
  └── template1
     ├── langs
     │   ├── en.php
     │   └── es.php
     └── vars
         ├── color1.php
         ├── color2.php
         └── color3.php
```
___
## 4. Running the script

Once the template file and the configuration files are correctly created, just run the script (`$ php generateTpl.php`), and it will create the /dist directory by itself, containing the final customized HTML files, similar to this:
```
dist
  └── template1
      ├── color1
      │    └── template1-en-color1.html
      ├── color2
      │    ├── template1-en-color2.html
      │    └── template1-es-color2.html
      └── color3
           └── template1-en-color3.html
```

You can see a sample template, with a sample configuration files showing how to use the twig syntax and how to format the configuration files.
___
## 5. Troubleshooting

The script outputs not only every made step, but and also every error found in the directory configuration.
1. If there are problems with the 'vendor' folder, make sure that the twig templates support is succesfully installed, or run `$ composer require twig/twig` in the command line to install it. It will create a '/vendor' folder and the 'autoload.php' within it.

2. If the issue is related to the configuration files or folders, make sure that you have followed these steps. Be careful with the names of the templates and the config folder names. 
    > Section 3: Creating the configuration files:
    >  "... If the template file is called 'template1.tpl', inside the '/config' folder must be a directory called 'template1'..."

    > Section 3: Creating the configuration files:
    >  "... If you add the item 'en' to the array, there must be a file inside '/config/langs' called 'en.php')

Anyways, the script will oputput some hints if something is not working properly.