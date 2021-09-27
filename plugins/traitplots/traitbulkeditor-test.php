<?php
# this submits to itself
# check get string, if unsset, emit form.
# if set, sanitize and perform bulk action.
?>
<html>
  <body>
    <label for="trait">Which trait:</label>
    <select name="trait">
      <option>"Repro"</option>
      <option>"Flowers open"</option>
      <option>"Fruits"</option>
    </select>
    <!-- <label for="trait">Choose a trait state:</label>
    <select name="state">
      <option>"Repro"</option>
      <option>"Flowers open"</option>
      <option>"Fruits"</option>
    </select> -->
    <label for="trait">Summary Method:</label>
    <select name="stats">
      <option>"By Month"</option>
    </select>
    <label for="trait">Plot type:</label>
    <select name="plot">
      <option>"Polar plot"</option>
      <option>"Scatterplot"</option>
      <option>"Barplot"</option>
      <option>"Boxplot"</option>
    </select>
    <input type="submit">
  </body>
</html>
<?php
 ?>
