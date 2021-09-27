To Do
=====
refactor for trait id not state id
- get all state ids and state names.
gather caption info.
finish by year and admin_div
do bar plot



Testing
=======

Taxa
----
Poppy | 18097
Bristlecone pine | 58426
Sago lily | 202611
Calicoflower | 203440
Red-leaf pondweed | 219861

SQL
---
SELECT o.month, COUNT(*) AS count FROM tmattributes AS a LEFT JOIN omoccurrences AS o ON o.occid = a.occid WHERE o.tidinterpreted = '203435' AND a.stateid = '2' GROUP BY o.month

SELECT * FROM `taxa` WHERE SciName LIKE 'Downingia%'

To do
=====
+ test for missing data (i.e. gaps) [Done] - predicts zero if between or displays no line if consecutively missing
+ test for divide by zero, e.g., 219862 --same as allzero below? [Done]
+ test for no all zero datavalues [Done]
+ test for empty/unset data array [Done] displays graph with no data or scale.
+ Other plot types:
  - BarPlot
  - ScatterPlot
  - BoxPlot

TraitPlotter
------------
+ test missing or invalid trait state [Done]
+ test for missing or invalid taxon [Done]
+ roll up taxonomy
+ limit to spp. and genera

+ Summary methods:
  - By month
  - Date, annually, over time

Bulk Create Plots
-----------------

+ Gather taxa, trait and states of interest, plot type.
+ Instantiate a TPDescEditorManager
+ For each taxon*, add a block per desired trait, add a statement per desired trait state.
+ populate statement with plot html/svg.

*only taxa that are "valid" or desired or selected.

How should this code be/get updated?
  Options:
  - Every time a TP is loaded.  <<<
  - On a schedule (e.g., nightly).
  - Manually by user via a refresh button.
  - Whenever an insert/update occurs to the tmattributes table.

6/25: New plan for bulk plots: use the custom taxa template and create a new element for trait plots.
Add vars to symbini file that encode trait number, plot type, and summary statistic.
  make this a string.
