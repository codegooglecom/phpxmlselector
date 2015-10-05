# Note #

  * I wrote this library for my project Queslite(http://queslite.com), it's a solution for questionnaire auto genarate. It translate user input into XML, then use xlst to translate XML into HTML to display.

  * Queslite is totally coded by PHP 5.2+, I wrote the xml selector for that to make a easy way to find XMLNode, set and get its value, attributes.

  * Use this library, you could select xml note like using jQuery. Although I just implement some simple features of jQuery, it would be useful for you while programming a xml base project. I hope this would be helpful. :)

  * #### PS: jQuery is a great js library. ####

# How to use select function #

```
function select($str)
```

  * #### return ####
> array, use selectSingle to return DOMElement
  * #### des ####
> depends on $str
  * #### argument example ####
```
<?xml version="1.0"?>
<lover>
	<person id="jennal" age="22">
		<favorite>love xiaoyu</favorite>
	</person>
	<person id="xiaoyu" age="21">
		<favorite>love jennal</favorite>
	</person>
</lover>
```
```
 "lover person" #will get an array of elements whose tagName is person
	 "person" #could do the same thing
 "#jennal" #will get an array of elements which has only one element, its id="jennal"
 "person[age=22]" #will get an array of elements whose age="22"
 "#jennal favorite" #will get an array of elements which are jennal's favorite
	 "person[age=22] favorite" #could do the same thing
```