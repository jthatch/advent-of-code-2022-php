## --- Day 18: Boiling Boulders ---

You and the elephants finally reach fresh air. You've emerged near the
base of a large volcano that seems to be actively erupting! Fortunately,
the lava seems to be flowing away from you and toward the ocean.

Bits of lava are still being ejected toward you, so you're sheltering in
the cavern exit a little longer. Outside the cave, you can see the lava
landing in a pond and hear it loudly hissing as it solidifies.

Depending on the specific compounds in the lava and speed at which it
cools, it might be forming
<a href="https://en.wikipedia.org/wiki/Obsidian"
target="_blank">obsidian</a>! The cooling rate should be based on the
surface area of the lava droplets, so you take a quick scan of a droplet
as it flies past you (your puzzle input).

Because of how quickly the lava is moving, the scan isn't very good; its
resolution is quite low and, as a result, it approximates the shape of
the lava droplet with *1x1x1
<span title="Unfortunately, you forgot your flint and steel in another dimension.">cubes</span>
on a 3D grid*, each given as its `x,y,z` position.

To approximate the surface area, count the number of sides of each cube
that are not immediately connected to another cube. So, if your scan
were only two adjacent cubes like `1,1,1` and `2,1,1`, each cube would
have a single side covered and five sides exposed, a total surface area
of *`10`* sides.

Here's a larger example:

    2,2,2
    1,2,2
    3,2,2
    2,1,2
    2,3,2
    2,2,1
    2,2,3
    2,2,4
    2,2,6
    1,2,5
    3,2,5
    2,1,5
    2,3,5

In the above example, after counting up all the sides that aren't
connected to another cube, the total surface area is *`64`*.

*What is the surface area of your scanned lava droplet?*

To begin, <a href="18/input" target="_blank">get your puzzle input</a>.

Answer:

You can also <span class="share">\[Share<span class="share-content">on
<a
href="https://bsky.app/intent/compose?text=%22Boiling+Boulders%22+%2D+Day+18+%2D+Advent+of+Code+2022+%23AdventOfCode+https%3A%2F%2Fadventofcode%2Ecom%2F2022%2Fday%2F18"
target="_blank">Bluesky</a> <a
href="https://twitter.com/intent/tweet?text=%22Boiling+Boulders%22+%2D+Day+18+%2D+Advent+of+Code+2022&amp;url=https%3A%2F%2Fadventofcode%2Ecom%2F2022%2Fday%2F18&amp;related=ericwastl&amp;hashtags=AdventOfCode"
target="_blank">Twitter</a> <a href="javascript:void(0);"
onclick="var ms; try{ms=localStorage.getItem(&#39;mastodon.server&#39;)}finally{} if(typeof ms!==&#39;string&#39;)ms=&#39;&#39;; ms=prompt(&#39;Mastodon Server?&#39;,ms); if(typeof ms===&#39;string&#39; &amp;&amp; ms.length){this.href=&#39;https://&#39;+ms+&#39;/share?text=%22Boiling+Boulders%22+%2D+Day+18+%2D+Advent+of+Code+2022+%23AdventOfCode+https%3A%2F%2Fadventofcode%2Ecom%2F2022%2Fday%2F18&#39;;try{localStorage.setItem(&#39;mastodon.server&#39;,ms);}finally{}}else{return false;}"
target="_blank">Mastodon</a></span>\]</span> this puzzle.
