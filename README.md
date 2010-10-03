**This documentation is not stable**


Introduction
============

This plugin is a fork of sfLucenePlugin originally written by Carl Vondrick, sfLucenePlugin is based on Zend_Search (php lucene implementation). sfSolrPlugin integrates Solr into symfony framework.

What Is Solr?
=============

Solr is the popular, blazing fast open source enterprise search platform from the Apache Lucene project. Its major features include powerful full-text search, hit highlighting, faceted search, dynamic clustering, database integration, and rich document (e.g., Word, PDF) handling. Solr is highly scalable, providing distributed search and index replication, and it powers the search and navigation features of many of the world's largest internet sites.

Solr is written in Java and runs as a standalone full-text search server within a servlet container such as Tomcat. Solr uses the Lucene Java search library at its core for full-text indexing and search, and has REST-like HTTP/XML and JSON APIs that make it easy to use from virtually any programming language. Solr's powerful external configuration allows it to be tailored to almost any type of application without Java coding, and it has an extensive plugin architecture when more advanced customization is required.
The first plugin available is sfLucenePlugin based on Zend_Search (the PHP version from Zend Framework).

source : http://lucene.apache.org/solr/

What is Lucene?
===============
Apache Lucene is a high-performance, full-featured text search engine library written entirely in Java. It is a technology suitable for nearly any application that requires full-text search, especially cross-platform.

source : http://lucene.apache.org/java/docs/


Requirements
============

 - Doctrine
 - symfony 1.[2,3,4]
 - Java
 - mbstring extension

Main Features
=============

 - Configured all by YAML files
 - Complete integration with symfony 1.2
 - i18n ready
 - Keyword highlighting
 - Management of Lucene indexes
 - (not anymore) 500+ unit tests and 98% API code coverage


Initialize the plugin
=====================

To initialize the solr configuration you have to run two commands

    ./symfony lucene:initialize frontend
    ./symfony lucene:create-solr-config frontend

The first command creates the search.yml files in the project configuration folder and in the application configuration folder.
The second command creates all files required by solr to run in a multicore (multi index) mode.

You can start the solr server with the following command

    ./symfony lucene:service frontend start

**Note:** the lucene:service task works only on macosx, linux or any other command.

For windows user, you need to start solr with theses commands :

    cd YOUR_PROJECT\plugins\sfLucenePlugin\lib\vendor\Solr\example\

    java -Dsolr.solr.home=YOUR_PROJECT\config\solr\ -Dsolr.data.dir=YOUR_PROJECT\data\solr_index -jar start.jar

The application container is the default one provided with solr : Jetty.

Jetty
-----

Jetty is an open-source project providing a HTTP server, HTTP client and javax.servlet container. These 100% java components are full-featured, standards based, small foot print, embeddable, asynchronous and enterprise scalable. Jetty is dual licensed under the Apache Licence 2.0 and/or the Eclipse Public License 1.0. Jetty is free for commercial use and distribution under the terms of either of those licenses.

more information about Jetty : http://www.mortbay.org/jetty/

Lucene Document
===============

A document is the definition of how data is indexed into lucene index. All documents share the same definition, so when you work with two different models, they both share the same definition. If you need to create specific field per model, you must prefix the name field with a letter.

The document properties are defined in the schema.xml file. The schema.xml file contains the different types names availables.

Types :

  - `text` : this field is filtered by  : solr.WhitespaceTokenizerFactory, solr.ISOLatin1AccentFilterFactory,  solr.StopFilterFactory, solr.WordDelimiterFilterFactory, solr.LowerCaseFilterFactory, solr.SnowballPorterFilterFactory, solr.RemoveDuplicatesTokenFilterFactory. This field should be fine for standard text search.
  - `string` : is not analyzed, but indexed/stored verbatim
  - `boolean`
  - `binary`: The data should be sent/retrieved in as Base64 encoded Strings
  - `int`, `float`, `long`,`double` : numeric field types
  - `tint`, `tfloat`, `tlong`,`tdouble` : numeric field types (use it for fast range queries)
  - `date` : is of the form 1995-12-31T23:59:59Z the trailing "Z" designates UTC time and is mandatory.
  - `tdate` : like `date` but faster date range queries and date faceting
  - `random` : is not used to store or search any data.  You can declare fields of this type it in your schema to generate pseudo-random orderings of your docs for sorting purposes.
  - `text_ws` : only splits on whitespace for exact matching of words

More information can be found be reading the default schema.xml file provided by solr.

There is only one schema.xml file per index. The plugin uses the multi core option so you can declare different schema.xml depends on your need.

Create the schema.xml file
==========================

The entire plugin is configured by search.yml files placed throughout your application.  You must be careful that you are aware of what search.yml file you are working in because each one has a different purpose.  As you will later learn, the project level search.yml file controls the entire engine while a module's search.yml defines indexing parameters.

Open your project's search.yml file, located in `myproject/config/search.yml`.  If you followed the installation instructions above, you will see:

    [yaml]
    MyIndex:
      models:


Similar to your schema.yml file, you can have multiple indexes.  The only requirement is that you must name them!  So, enter a name for the first index, where "MyIndex" goes.  

If you require i18n support, you must define the cultures that you support under index.  Use the following syntax:

    [yaml]
    index:
      cultures: [en_US, fr_FR]

(If you receive an exception saying "Culture XXX is not enabled" then define the culture even if you do not use i18n.)

Before indexing your models you need to declare which fields to index. These fields are defined in the `search.yml` files.

    [yaml]
    myIndex:
      models:
        sfGuardUserProfile:
          fields:
            id: { type: int, stored: true }
            full_name:
              stored: true
            name:
              stored: false
              alias: getFullName
            p_full_name:
              stored: true
              boost: 3        # set the boost and enable omitNorms
              alias: getFullName
            p_description:
              stored: true
              transform: strip_tags
              alias: getDescription
              omitNorms: false # enable boost
            location_latitude:
              type: tfloat
              stored: false
              default: 0
            location_longitude:
              type: tfloat
              stored: true
              default: 0
            location_address:
              stored: true
          validator: isIndexable

        Address:
          fields:
            id: { type: int, stored: true }
            name:
              stored: true
            a_name:
              stored: true
              alias: getName
            location_latitude:
              type: tfloat
              stored: false
              default: 0
            location_longitude:
              type: tfloat
              stored: true
              default: 0
            location_address:
              stored: true

      index:
        encoding: UTF-8
        cultures: [fr]
        host: 127.0.0.1
        port: 8983
        base_url: "/solr"

Further, you can specify a transformation function to put the value through before it is indexed.  This is useful if you have HTML code being returned and you need strip it out. Note that if no transform is defined for a boolean field, then true/false is automatically converted to 'true'/'false'. A transform is define this like so:

    [yaml]
    myIndex:
      models:
        mymodel:
          fields:
            id: { type: int, stored: true }
            name:
              stored: true
              transform: [myconverterclass, convertMethod]

Everytime you update the search.yml file, the `create-solr-conf` command must be run. The server must be restarted too. Depending on how deep is your update, you *might* need to reindex or to delete the index.

Furthermore you can customize the schema.xml, by modifying the generated base_schema.xml. Note that Solr provides countless additional options and that the default base_schema.xml focuses on cleanness rather than illustrating all possibilities. Check the Solr documentation or the Solr example core in the vendor dir of this plugin for more details.

There is automatic support for title and description fields that is used by the bundled module, which will be indexed in solr as sfl_title/sfl_description. In case the plugin cannot guess the names for these fields, you can specify the explicitly.

    [yaml]
    myIndex:
      models:
        mymodel:
          title: some_col
          description: some_col2

Indexing options
================

Query handling
--------------

Solr uses the solrconfig.xml to define what query handlers are defined as well as various caching settings. The base template can be configured via the base_solrconfig.xml in order to for example add a dismax query handler.

Base query
----------

You can create a `getLuceneQuery` method into your ModelTable or ModelPeer to customize the SQL query used to fetch the object. For instance you can add a left join to avoid too many queries or to remove some model from being indexed.

Model::isIndexable()
--------------------

You can create an `isIndexable` method into your model so your model can define if it can be indexed by solr. This can be usefull if you have some security requirements based on some ACLs.

Edit the search.yml file and add into a model section the following line :

    validator: isIndexable

Model::getLuceneDocument()
--------------------------

The model can also provide a base lucene document if the search.yml can not match your requirement. The plugin will get the base document from the model and then fetch the value of the different fields defined in the search.yml file.
Relation and multivalue fields

A multivalue field is like a php array with one dimension. So you can declare a field as `multivalued`, the plugin will automatically create the array :

 - the field is a Relation : loop over the relation and call the `__toString()` method
 - the field is an array : the value is stored into the document

If the `multivalued` is not defined and if the field return a Relation or an array then the plugin will raise an error.

*TODO : the propel version does not exist*


Indexing
===============

sfLucene currently supports two ways to add information to the index:
  1. Through the ORM layer
  2. Through symfony actions

Through the ORM layer is the recommended method to add information to the index.  The plugin can keep the index synchronized if you use the ORM layer.  Through symfony actions is intended only for static content, such as the privacy policy.

Indexing models
---------------

There are two commands available to index models

  - `lucene:update-model` : index all models defined in the search.yml file. This command should be used with very large index as php suffers from memory leak.
  - `lucene:update-model frontend index fr` : index models
  - `lucene:update-model frontend index fr Address` : index only the Address model
  - `lucene:update-model frontend index fr Address --delete=true --limit=100 --offset=100` : delete all information for the Address model and reindex model from the offset 100 with a limit of 100
  - `lucene:update-model-system` : index all models defined in the search.yml by using sub process in order to avoid memory leak.
  - `lucene:update-model-system frontend index fr` : index models
  - `lucene:update-model-system frontend index fr --delete=true` : delete the index and index models
  - `lucene:update-model frontend index fr --model=Address` : index only the Address model
  - `lucene:update-model frontend index fr --model=Address --delete=true` : delete Address index and only the Address model

*TODO : The last command needs to be implemented in PropelIndexer*

When search results are displayed, the system intelligently guesses which field should be displayed as the result "title" and which field is the result "description." However, to be explicit, you can specify a description and title field, as in sfGuardUserProfile.

Note that the fields do not have to exist as fields in your database.  As long as it has a getter on the model, you can use it in your index.  The fields are automatically camelized, so if you wish to call "->getSuperDuperMan()" as one of your fieds, you must write it in the YAML file as "super_duper_man".

Next, you must tell your application where to route the model when it is returned.  You do this by opening your application's config/search.yml file and defining a route:

    [yaml]
    MyIndex:
      models:
        BlogPost:
          route: blog/showPost?id=%id%
        BlogComment:
          route: blog/showComment?id=%id%

In routes, %xxx% is a token and will be replaced by the appropriate field value.  So, %id% will be the value returned by the ->getId() method.  Warning: You must also define the field in the project's search.yml to be indexed or unexpected results will occur!

Finally, you must register the model with the system.

Advanced Model Settings
-----------------------

You can configure the model even more.  If the peer does not follow symfony's naming conventions, you can specify a new one with in the project level search.yml:

    [yaml]
    MyIndex:
      models:
        BlogPost:
          peer: OtherPeer


Further, sfLucene optimizes memory usage when rebuilding the index from the database by using both an internal pager and hydrating objects on demand.  By default, rows are selected in batches of 250, but if you require this to be different, you can customize it like so:

    [yaml]
    MyIndex:
      models:
        BlogPost:


If only some of your objects should be stored in the index, you can define an validating method on the model that can return a boolean indicating whether the model should be indexed.  If this method returns true, the indexer proceeds with indexing.  If the method returns false, the indexer ignores that particular instance.  By default, the indexer looks for an "isIndexable" method and calls it if it is available.  However, you can specify your own method like so:

    [yaml]
    MyIndex:
      models:
        BlogPost:
          validator: should_index

Indexing actions
---------------

*TODO : not up-to-date or tested*

To setup an action to be indexed, you must create a file in the module's config directory named search.yml.  Inside this file, you define the actions you want indexed:

    [yaml]
    MyIndex:
      privacy:
      tos:
        security:
          authenticated: true
          credentials: [admin]
      disclaimer:
        params:
          advanced: true
        layout: true

Remember to prefix each one with the name of the index.



Model Behavior
==============

Doctrine
--------

You can attach a listener into your doctrine model in order to update the lucene index. In the `actAs` section just add `sfLuceneDoctrineTemplate`

    [yaml]
    Address:
      tableName: addresses
      options: { charset: utf8, collate: utf8_unicode_ci }
      actAs:
        sfLuceneDoctrineTemplate:
      columns:
        id: { type: integer(4), primary: true, autoincrement: true }
        name: { type: string(40), }
        location_longitude:  { type: float }
        location_latitude:   { type: float }
        location_address:   { type: string(255) }

**Note:** if solr fails to save the document and a sfContext object exists then the listener will silently ignore the error.

Propel
------

*TODO : need to be fixed*

Search
======

Search is done through the `sfLuceneCriteria` object. Once the criteria is created you can get the result a lucene instance. A lucene instance is defined by a name, a culture and an optional `sfConfigurationApplication` object (if done is provided, the plugin uses the default active configuration available).

simple search
-------------

    [php]
    // build the lucene criteria
    $criteria = new sfLuceneCriteria;
    $criteria
      ->addPhrase('more with symfony')
      ->setLimit(10)
      ->select('id, name, score');

    // retrieve the lucene instance
    $lucene = sfLucene::getInstance('index', 'fr');

    // retrieve the results
    $sf_lucene_results = $lucene->friendlyFind($criteria);

    // in a template file
    <ul>
      <?php foreach($sf_lucene_results as $result): ?>
          <li><?php echo $result->getName() ?></li>
      <?php endforeach; ?>
    </ul>

search with pager
-----------------

    [php]
    // use the previous code
    $pager = new sfLucenePager($sf_lucene_results);


    // in the template file
    <?php foreach ($pager->getResults() as $result): ?>
      <li>
        <?php echo link_to($result->getInternalTitle(), $result->getInternalUri()) ?>
        <br />
        <?php echo $result->getInternalDescription() ?>
      </li>
    <?php endforeach ?>

    <?php if ($pager->haveToPaginate()): ?>
      <div class="search-page-numbers">
        <?php if ($pager->getPage() != $pager->getPreviousPage()): ?>
          <a href="<?php echo url_for($url) ?>?<?php echo $form->getQueryString($pager->getPreviousPage()) ?>" class="bookend">Previous</a>
        <?php endif ?>

        <?php foreach ($pager->getLinks($radius) as $page): ?>
          <?php if ($page == $pager->getPage()): ?>
            <strong><?php echo $page ?></strong>
          <?php else: ?>
            <a href="<?php echo url_for($url) ?>?<?php echo $form->getQueryString($page) ?>"><?php echo $page ?></a>
          <?php endif ?>
        <?php endforeach ?>

        <?php if ($pager->getPage() != $pager->getNextPage()): ?>
          <a href="<?php echo url_for($url) ?>?<?php echo $form->getQueryString($pager->getNextPage()) ?>" class="bookend">Next</a>
        <?php endif ?>
      </div>
    <?php endif ?>

Search with filtering
---------------------

When a search is performed, solr looks through the index to find matches. You can filter entries from the index so the search is executed quickier.

    [php]
    // create the query option
    $criteria = new sfLuceneCriteria;
    $criteria->addSane($keywords);
    $criteria->addFiltering('sfl_model', 'Address');

    // retrieve the lucene instance
    $lucene = sfLucene::getInstance('index', 'fr');

    // retrieve the results
    $sf_lucene_results = $lucene->friendlyFind($c);

Solr experts
------------

All Solr searching features are not implemented into `sfLuceneCriteria` object. However you can add extra parameters with the `addParam` method.

    [php]
    // create a criteria object and define the query handler to 'dismax'
    $criteria = new sfLuceneCriteria;
    $criteria
       ->addParam('qt', 'dismax')
       ->addSane($keywords);

Faceted Search
--------------

"Faceted search, also called faceted navigation or faceted browsing, is a technique for accessing a collection of information represented using a faceted classification, allowing users to explore by filtering available information. A faceted classification system allows the assignment of multiple classifications to an object, enabling the classifications to be ordered in multiple ways, rather than in a single, pre-determined, taxonomic order. Each facet typically corresponds to the possible values of a property common to a set of digital objects."

source : http://en.wikipedia.org/wiki/Faceted_search

There are two types of facet :
  - field : count is done on the different value a field can have
  - query : the count is the result of a query

Usage:

    [php]
    $criteria = new sfLuceneFacetsCriteria;
    $criteria
       ->addFacetField('sfl_model')
       ->addFacetQuery('first_letter:[A TO M]')
       ->addFacetQuery('first_letter:[N TO Z]')
       ->addSane($keywords);

The search results will be composed of :
  - a list of result for the provided $keywords
  - a list of count depending on the facet provided and the $keywords

    [php]
    $results = $lucene->friendlyFind($criteria);

    // get the result from the 'sfl_model' facet
    $model_counts = $results->getFacetField('sfl_model');

    // get the result from the one query
    $letter_group = $results->getFacetQuery('first_letter:[A TO M]');

    // get all queries results
    $queries_counts = $results->getFacetQueries();

    print_r($queries_counts);
    // array('first_letter:[A TO M]' => 12, 'first_letter:[N TO Z]' => 34);


Geolocated Search with localsolr
--------------------------------

LocalSolr is a port of the LocalLucene library to the Solr search server. LocalSolr offers geographical searching capabilities to your search engine.
See http://www.nsshutdown.com/projects/lucene/whitepaper/locallucene_v2.html

First, enable localsolr in your search.yml file (and run lucene:create-solr-config task again)

    [yaml]
      myIndex:
        models:
          (...)
        index:
          (...)
          localsolr:
            enabled: true
            # latitude_field: lat
            # longitude_field: lng

Usage (kilometers):

    [php]
    $lucene = sfLucene::getInstance('index', 'fr');
    
    $criteria = new sfLuceneGeoCriteria();
    $criteria->addGeoCircle(48.8951187, 2.2876496, 2); // 2 km
    $criteria->addAscendingSortByDistance();
    
    $results = new sfLuceneGeoResults($lucene->find($criteria), $lucene);
    
    foreach($results as $result)
    {
      echo sprintf("distance between me and %s : %f km\n", $result->getName(), $result->getGeoDistance());
    }
    
Usage (miles):

    [php]
    $lucene = sfLucene::getInstance('index', 'fr');

    $criteria = new sfLuceneGeoCriteria(sfLuceneGeoCriteria::UNIT_MILES);
    $criteria->addGeoCircle(48.8951187, 2.2876496, 2); // 2 miles
    $criteria->addAscendingSortByDistance();

    $results = new sfLuceneGeoResults($lucene->find($criteria), $lucene, sfLuceneGeoCriteria::UNIT_MILES);

    foreach($results as $result)
    {
      echo sprintf("distance between me and %s : %f miles\n", $result->getName(), $result->getGeoDistance());
    }

This will filter any result that is located outside the circle. If you dont't want to filter the results
(for example if you want to see ALL results sorted by ascending distance) you need to set a very big radius.


Build in interface
==================

sfLucene ships with a basic search interface that you can use in your application.  Like the rest of the plugin, it is i18n ready and all you must do is define the translation phrases.

To enable the interface, open your application's settings.yml file and add "sfLucene" to the enabled_modules section:

    [yaml]
    all:
      .settings:
        enabled_modules: [default, sfLucene]

If you have specified multiple indexes in your search.yml files, you need to configure which index that you want to search.  You do this by opening the app.yml file and adding the configuration setting:

    yaml]
    all:
      lucene:
        index: MyIndex


Customizing the Interface
-------------------------

As every application is different, it is easy to customize the search interface to fit the look and feel of your site. Doing this is easy as all you must do is overload the templates and actions.

Creating a Skeleton Module 
--------------------------

To get started, simply run the following on the command line:

    [yaml]
    $ symfony lucene:init-module myApp

If you look in myapp's module folder, you will see a new sfLucene module.  Use this to customize your interface.

The lucene:init-module task is capable of custom module names and linking each module to a certain index.  This makes it possible to have multiple search interfaces in the same application.  To do this, simply run the above command with two extra parameters:

    [yaml]
    $ symfony lucene:init-module myApp myLucene myIndex


The above will create a skelelton module called "myLucene" in the application "myApp" and configure this module to search off the index "myIndex".

Customizing Results
-------------------

Often, when writing a search engine, you need to display a different result template for each model.  For instance, a blog post should show differently than a forum post.  You can easily customize your results by changing the "partial" value in your application's search.yml file.   For example:

    [yaml]
    models:
      BlogPost:
        route: blog/showPost?slug=%slug%
        partial: blog/searchResult
      ForumPost:
        route: forum/showThread?id=%id%
        partial: forum/searchResult


For ForumPost, the partial apps/myapp/modules/forum/templates/_searchResult.php is loaded.  This partial is given a $result object that you can use to build that result.  The API for this object is pretty simple:

  - `$result->getInternalTitle()` returns the title of the search result.
  - `$result->getInternalRoute()` returns the route to the search result.
  - `$result->getXXX()` returns the XXX field.

In addition to the $result object, it is also given a $query string, which was what the user searched for.  This is useful for highlighting the results.

Advanced Search
---------------

If you wish to disable the advanced search interface, open the application's app.yml file and add the following:

    [yaml]
    all:
      lucene:
        advanced: off


This will prevent sfLucene from giving the user the option to use the advanced mode.

Routing
-------

*TODO : review this part*

Note: This is currently broken because symfony 1.1 does not currently support this.  The symfony god, Fabien, is planning to fix this.  Until then, you have to register your own routes.  See ticket #2408 and symfony-devs mailing list.

sfLucene will automatically register friendly routes with symfony.  For example, surfing to `http://example.org/search` will route to sfLucene.  If you would like to customize these routes, you can disable them in the app.yml file with:

    [yaml]
    all:
      lucene:
        routes: off


It will then be up to you configure the routing.

Pagination
----------

You can customize pages by using the same logic as above (defaults to 10):

    [yaml]
    all:
      lucene:
        per_page: 10

To customize the pager widget that is displayed, change the pageradius key (defaults 5):

    [yaml]
    all:
      lucene:
        pager_radius: 5


Results
-------

You can configure the presentations of the search results. If you require more fine-tuned customizations, you are encouraged to create your own templates.

To change the number of characters displayed in search results, edit the "resultsize" key:

    [yaml]
    all:
      lucene:
        result_size: 200

To change the highlighter used to highlight results, edit the "resulthighlighter" key:

    [yaml]
    all:
      lucene:
        result_highlighter: |
          <strong class="highlight">%s</strong>

Highlighting Pages
------------------

*TODO : review this part*

The plugin has an optional highlighter than will attempt to highlight keywords from searches.  The highlighter will hook into this search engine and also attempts to hook into external search engines, such as Google and Yahoo!.

To enable this feature, open the application's config/filters.yml file and add the highlight filter before the cache filter:

    [yaml]
    rendering: ~
    web_debug: ~
    security:  ~

    # generally, you will want to insert your own filters here

    highlight:
      class: sfLuceneHighlightFilter

    cache:     ~
    common:    ~
    flash:     ~
    execution: ~


By default, the highlighter will also attempt to display a notice to the user that automatic highlighting occured.  The filter will search the result document for `<!--[HIGHLIGHTER_NOTICE]-->` and replace it with an i18n-ready notice (note: this is case sensitive).

To highlight a keyword, it must meet the following criteria:

 - must be X/HTML response content type
 - response must not be headers only
 - must not be an ajax request
 - be inside the `body` tag
 - be outside of `textarea` tags
 - be between html tags and not in them
 - not have any other alphabet character on either side of it

To efficiently achieve this, the highlighter parser assumes that the content is well formed X/HTML.  If it is not, unexpected highlighting will occur.

The highlighter is also highly configurable.  The following filter listing shows the default configuration settings and briefly explains them:

    [yaml]
    highlight:
      class: sfLuceneHighlightFilter
      param:
        check_referer: on # if true, results from Google, Yahoo, etc will be highlighted.
        highlight_qs: sf_highlight # the querystring to check for highlighted results
        highlight_strings: [<strong class="highlight hcolor1">%s</strong>] # how to highlight terms.  %s is replaced with the term
        notice_tag: "<!--[HIGHLIGHTER_NOTICE]-->" # this is replaced with the notice (removed if highlighting does not occur)
        notice_string: > # the notice string for regular highlighting.  %keywords% is replaced with the keywords.  i18n ready.
          <div>The following keywords were automatically highlighted: %keywords%</div>
        notice_referer_string: > # the notice string for referer highlighting.  %keywords% is replaced with the keywords, %from% with where they are from,.  i18n ready
          <div>Welcome from <strong>%from%</strong>!  The following keywords were automatically highlighted: %keywords%</div>


If you need to configure it more, it is possible to extend the highlighting class.  Refer to the API documentation for this.

Note: If you experience extremely slow page response times when using the highlighting filter (300ms to 2000ms), then you are recommended to reconfigure your XML catalog.  For instructions on how to do this, open the tarball XMLCatalog.tar.gz and follow the instructions there.

Categories
----------

*TODO : review this part*

Each document in the index can be tied to one or more categories.  It is then possible to limit your search results to that category in the provided interface.  To enable this, you must define a "categories" key to your models or actions.  For instance, an example model:

    [yaml]
    models:
      Blog:
        fields:
          title: text
          post: text
          category: text
        categories: [%category%, Blog]


The "Blog" model above will be placed both into the blog category and the string returned by ->getCategory() on the model. After you rebuild your model, a category drop down will automatically appear on the search interface.

The same rules applies as model indexing: Note that the fields do not have to exist as fields in your database.  As long as it has a getter on the model, you can use it in your index.  The fields are automatically camelized, so if you wish to call "->getSuperDuperMan()" as one of your fieds, you must write it in the YAML file as "super_duper_man".

To disable category support all-together, open the application level app.yml file and add:

    [yaml]
    all:
      lucene:
        categories: off


This will prevent sfLucene from giving the user an option to search by category.

Integrating sfLucene with another plugin
----------------------------------------

*TODO : review this part*

It is possible to integrate sfLucene with other plugins.  To add support to your Propel models, you must append the following:

    [php]
    if (class_exists('sfLucene', true))
    {
      sfLucenePropelBehavior::getInitializer()->setup('MyModel');
    }


The conditional lets your plugin function should the user not have this plugin installed.

Then, you must configure sfLucene with your plugin.  In project/plugins/sfMyPlugin/config/search.yml, you can define the settings for your models.  You can also create a search.yml file in your modules file.  But, be warned that these files can be overloaded by the user.

Updating a model's index when a related model changes
-----------------------------------------------------

*TODO : review this part*

If a model's index should be updated based on the modification of a related model, you can override the save method of the related objects to directly call the sfLucene saveIndex and/or deleteIndex methods as in the example below:

    [php]
    class Bicycle extends BaseBicycle
    {
      public function save()
      {
        parent::save();

        foreach ($this->getWheels() as $wheel)
        {
          $wheel->saveIndex();
        }
      }
    }


