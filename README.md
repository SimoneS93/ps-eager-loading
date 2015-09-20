# ps-eager-loading
If you experience some slowness on some of your pages, mainly those with lots of products (like the category page), it can be because of an inefficient querying strategy from Prestashop to the database. Here I try to help you solve this hard problem.

###LAZY LOADING vs EAGER LOADING

Prestashop aways uses lazy loading when querying the database: this means it runs a query for each entity (product, specific price, group reduction) it needs and this, meant to save space in the memory, turns out to be a really bad strategy. So I thought it would be better to implement an eager loading strategy (querying data in batch) and see the differences.

This requires you to implement eager loading in your DbQuery class with an override and edit all of the places where you experience database bottlenecks.
