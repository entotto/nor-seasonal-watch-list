# Seasonal Watch List

[Seasonal Watch List](https://swl.nearlyonred.com) is an application used by the Nearly On Red (NOR) community's
membership to track watch activity and recommendations of seasonal anime. Every three months a new batch of shows
becomes available. The NOR community uses this application to track which shows they are watching,
and which shows they think are worth the interest and attention of others in the community.
The [Nearly On Red Discord server](https://discord.com/channels/592858084339351554/596489195841912873) is the center for
discussion about these shows within the community. Each season the community selects a limited number of shows that are
likely to generate enough discussion to warrant having their own individual channels for those posts. Show selection is
a mostly democratic process, made through one or more rounds of voting. This SWL application includes an election
feature, to organize those rounds of voting and collect the results.

Main features of the application:

* Personal watch list: each user indicates whether they are watching or intend to watch a given show, and how strongly
  they believe the show will interest others in the community (or not).
* Community watch list: all individual watch activity and recommendation information is aggregated for each show and
  displayed in this section. The list can be sorted by activity count and average recommendation level, to quickly see
  which shows during the season are generating the most interest.
* Vote: once each season is far enough along that a few episodes are available for each show, an election is held to
  pick the shows most deserving of their own discussion channels in the Discord. This section displays the elections
  when they are open, and collects the voting results.

The application uses each community member's Discord account to log them in to the application, for purposes of keeping
their personal watch list entries and election votes separate from others.

## Contributing

Problem reports, feature requests, and code contributions are welcome. Submit
an [issue](https://github.com/Unheppcat/nor-seasonal-watch-list/issues), post
a [pull request](https://github.com/Unheppcat/nor-seasonal-watch-list/pulls), or contact
the [lead developer](mailto:unheppcat@yahoo.com) directly.

This application is built in PHP using the Symfony application framework. The Bootstrap CSS toolkit and a little bit of
JavaScript, both vanilla and jQuery, implement the user interface.
