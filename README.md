**Important Notice:**

This plugin is provided "as is" and is not officially supported. While we strive to ensure its functionality and compatibility, we make no guarantees regarding its performance or reliability. Users are encouraged to test the plugin thoroughly before using it in a production environment.

By using this plugin, you acknowledge that:

The plugin may have bugs, issues, or limitations that could affect its performance.
We are not responsible for any damage or loss that may result from using this plugin.
We do not offer any formal support or updates for this plugin.
Any use of this plugin is at your own risk.
Thank you for understanding.


==membership based==

Example shortcodes:

[mm_adv_access_decision membershipid='7' days='10' date='7/1/2019' access='true']

This content will be visible if either of these is true:
1. User has membership ID of 7 and subscribed to the website before the 1st of July 2019.
2. User has membership ID of 7 and the number of days since the user subscribed to the website is greater or equal to 10.
3. User has membership ID of 7 and subscribed to the website before the 1st of July 2019 and the number of days since the user subscribed to the website is greater or equal to 10.

[/mm_adv_access_decision]

[mm_adv_access_decision membershipid='7' date='7/1/2019' access='false']

This content will be visible if this is true:

User has membership ID of 7 and subscribed to the website after the 1st of July 2019.

[/mm_adv_access_decision]


[mm_adv_access_decision membershipid='7' days='10' date='7/1/2019' access='future']

When access='future' then you can use a content like this one:
"You will have access to this content in [X] days."

The above content will be visible if this is true:

User has membership ID of 7 and subscribed to the website after the 1st of July 2019 and the number of days since the user subscribed to the website is smaller than 10. In this case, [X] will be replaced with the remaining number of days till 10.

[/mm_adv_access_decision]


==bundle based==

[mm_adv_access_decision bundleid='7' days='10' date='7/1/2019' access='true']

This content will be visible if either of these is true:
1. User has bundle ID of 7 assigned before the 1st of July 2019.
2. User has bundle ID of 7 assigned and the number of days since the bundle was assigned is greater or equal to 10.
3. User has bundle ID of 7 assigned before the 1st of July 2019 and the number of days since the bundle was assigned is greater or equal to 10.

[/mm_adv_access_decision]

[mm_adv_access_decision bundleid='7' date='7/1/2019' access='false']

This content will be visible if this is true:

User has bundle ID of 7 assigned and the bundle was assigned after the 1st of July 2019.

[/mm_adv_access_decision]


[mm_adv_access_decision bundleid='7' days='10' date='7/1/2019' access='future']

When access='future' then you can use a content like this one:
"You will have access to this content in [X] days."

The above content will be visible if this is true:

User has bundle ID of 7 assigned and the bundle was assigned after the 1st of July 2019 and the number of days since the bundle was assigned is smaller than 10. In this case, [X] will be replaced with the remaining number of days till 10.

[/mm_adv_access_decision]
