# Angular Firebase Sitemap Generator

Using this repo will make you capable of generating sitemap file for your very own AngularJS site powered by Firebase. Basically, using that simple scripts you should find helpful when dealing with a big site consisting of subpages generated dynamically over time.

For instance you may consider that helpful when making websites with some content shaped by your visitors i.e. user-created offers site

Whole issue comes down to generating:
- static pages (which are created once-and-forever by JS code in _states_generator.js_)
- dynamic pages (which are generated by providing certain params to a state)

My script gets all those pages and saves their URLs to _sitemap.xml_ file in the root directory of your site.


## static pages

Static pages are not being added & removed over time. Those might be subpages like "add product", "contact". "terms of use" and so on. Obviously content of the sites may be changing over time and even updating dynamically from Firebase! The only issue making a clear distinction here is URL Address of the specific subpage - it doesn't change over time.

**To generate URLs for static pages** you have to include JS code from _states_generator.js_ somewhere in your script and afterwards execute it. If there are some states you would like to disable from sitemap's URL list (i.e. moderator view) you may edit **disabledStates** array. To do so, simply put states' names in it


## dynamic pages

Dynamic pages are those changing over time. For instance if your site is some kind of hottest-news publisher and your editors create dozens of news every day - every article will be considered a dynamic page. It may exists now, but it hasn't even been planned yesterday...

**To generate URLs for dynamic pages** you have to edit your _sitemap_generator.php_ file:

_DATABASE_URL_ constant is where you input your firebase url

_SITE_URL_ - your site's address

_DEFAULT_TOKEN_ - your firebase secret token

_STATES_PATH_ - static pages path in your firebase. By default "/states"

And your subpages are included in 2 arrays:

**_$additionals_parent_** (which elements may consist of "path", "start_url", "end_url", and "condition").
- "path" is a path of child in FB (i.e. "products")
- "start_url" is your url's first part (i.e. if you think about website _example.com/news/another-winner-of-our-lottery_ where you have a state named "news" with url defined as "news:newsURL" in ui-router - start_url will be "news/")
- "end_url" the key you're looking for in your database. For instance if you have a tree "News" with children "title", "date", "author", "content", "seo_address" - it will be "seo_address"
- "condition" (optional) - some conditions to fetch your record. I.e. if you want to get URLs only of those news which are of 'status' == 'active' in your database you make condition an array:
	```
    "condition" => array(
				array(
					"first_operand" => "status",
					"operator" => "==",
					"second_operand" => "active"
				)
			)
    ```

and

**_$additionals_child_** (which elements may consist of "parent_path", "selector_path", "start_url", "end_url", and "condition").
- "parent_path" is a path of a parent in a database tree (i.e. if you have a structure like root->news->featured_news and you want to fetch the featured news - it should be "news")
- "selector_path" - is a child of a parent path you want to search in (i.e. if you want to fetch all the subcategories in every news category - you put 
```
"selector_path" => array(
				"subcategories"
			)
```
- "start_url", "end_url", "condition" - look @ $additionals_parent



Remember, you can always set a cron job to that _sitemap_generator.php_ file to generate you sitemap whenever you want!
