>       __                 __ 
>  ____/ /__  ____  ____  / /_
> / __  / _ \/ __ \/ __ \/ __/
>/ /_/ /  __/ /_/ / /_/ / /_  
>\__,_/\___/ .___/\____/\__/ 
>         /_/  
	
Depot is a small data parser that takes data from [NYC Open data](http://nycopendata.socrata.com) and turns it into a _(hopefully)_ more useful form. 

Depot takes the list of all subway stations from the Subway Stations data set, sorts them by line, attempts to sort them and returns them in JSON format. 

The API supports queries for individual, multiple, or all lines. 

###Example usage: 
flag.st/subways _// returns all lines_

flag.st/subways?line=A _//returns all stations on line A_

flag.st/subways?line=F-Q _//returns all stations two lines, grouped by line_

flag.st/subways?line=Q-F _//returns the exact same as above (e.g. queries are sorted by before being performed... caching and the likes)_

Depot has built in source data and result caching to improve performance. Currently if source data caching is enabled, Depot will retrieve a copy data once a day. Cached results will be regenerated once daily as well. Both caching types can be disabled. 

You should leave caching enabled for two reasons: 
- NYC Open Data forbids using their APIs when distributing software. 
- The data is more or less static (new stations aren't opening and closing frequently)
- It's (up to) 5x faster to serve cached results than creating them again.
- Sometimes the source data isn't available for multiple hours. 
- Depending on your PHP configuration and hosting, processing lots of data (or exceeding the PHP memory limit) could violate your 'simple web hosting' plan. Caching will reduce the chance anyone (or any pesky robots) will notice. 

##Known problems with Depot:
Some subway lines fork and turn into double headed snakes (see A south), the station sorting algorithm will usually sort one head correctly (the terminal station will not be recognized as a terminal station), the stations of the other head may not be in order.

Some lines are chopped up snakes (see S), which means they are not logically connected. The sorting algorithm assumes they are connected. 