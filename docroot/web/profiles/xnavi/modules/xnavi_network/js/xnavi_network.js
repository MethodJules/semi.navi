(function ($, Drupal, drupalSettings) {
    let initialized;
    let noNetworks = 0;
    function init() {
        if(!initialized) {
            initialized = true;

            const terms = drupalSettings.term;
            for (const [nid, term] of Object.entries(terms)) {
              createNetwork(term, nid);
              noNetworks++;
            }
        }
    };

    function createNetwork(term, nid) {
      const w = 1500;
      const h = 700;
      const linkDistance=150;
      const div = 'network_container_' + nid;

      d3.json(`${drupalSettings.base_url}/network/data/${term}`, function(data) {
        if (!$.isEmptyObject(data)) {
          const dataset = data;
          const praedikate = dataset.praedikate;
          const colors = d3.scale.category20();

          let svg = d3.select('#' + div)
            .classed("svg-container", true)
            .append("svg")
            //.attr({"width":w,"height":h})
            // Responsive SVG needs these 2 attributes and no width and height attr.
            .attr('width', '100%')
            .attr('height', '100%')
            .attr("preserveAspectRatio", "xMinYMin meet")
            .attr("viewBox", "0 0 " + w + " " + h)
            // Class to make it responsive.
            .classed("svg-content-responsive", true)
            .call(d3.behavior.zoom().on("zoom", function () {
              svg.attr("transform", "translate(" + d3.event.translate + ")" + " scale(" + d3.event.scale + ")")
            }))
            .append("g")

          let force = d3.layout.force()
            .nodes(dataset.nodes)
            .links(dataset.edges)
            .size([w,h])
            .linkDistance([linkDistance])
            .charge([-500])
            .theta(0.1)
            .gravity(0.05)
            .start();



          let edges = svg.selectAll("line")
            .data(dataset.edges)
            .enter()
            .append("line")
            .attr("id",function(d,i) {return 'edge'+i})
            .attr('marker-end','url(#arrowhead)')
            .style("stroke","#0099BE")
            .style("pointer-events", "none");

          let nodes = svg.selectAll("circle")
            .data(dataset.nodes)
            .enter()
            .append("a").attr("xlink:href", function(d){return d.link;})
            .append("circle")
            .attr({"r":15})
            .style("fill",function(d,i){return d.type === 'word' ? '#0099BE' : colors(i);})
            //.style("fill", "green")
            .call(force.drag)
            //.on('dblclick', connectedNodes);


          let nodelabels = svg.selectAll(".nodelabel")
            .data(dataset.nodes)
            .enter()
            .append("a").attr("xlink:href", function(d){return d.link;})
            .attr({"type":function(d){return d.type;}})
            .append("text")
            .attr({"class":"nodelabel",
              'font-size':'1.1em',
              'font-weight':'200',
              "stroke":"black",
              "dx": "0em",
              "dy": "2em",
              "text-anchor": "middle",
            })
            .text(function(d){return d.name;});

          let nodeicons = svg.selectAll(".nodeicon")
            .data(dataset.nodes)
            .enter()
            .append("text")
            .attr({"class":"nodeicon fas",
              'font-size':12,
              "stroke":"black",
              "dx": "0",
              "dy": "0.5em",
              "text-anchor": "middle",
            })
            .text(function(d){
              if (d.type === 'word') {
                return '\uf542';}
              });

          let edgepaths = svg.selectAll(".edgepath")
            .data(dataset.edges)
            .enter()
            .append('path')
            .attr({'d': function(d) {return 'M '+d.source.x+' '+d.source.y+' L '+ d.target.x +' '+d.target.y},
              'class':'edgepath',
              'fill-opacity':0,
              'stroke-opacity':0,
              'fill':'blue',
              'stroke':'red',
              'id':function(d,i) {return 'edgepath'+i}})
            .style("pointer-events", "none");

          // don't add edge labels if multiple networks are displayed on one page
          // to increase performance
          let edgelabels;
          if (noNetworks === 1) {

            edgelabels = svg.selectAll(".edgelabel")
              .data(dataset.edges)
              .enter()
              .append('text')
              .style("pointer-events", "none")
              .attr({'class':'edgelabel',
                'id':function(d,i){return 'edgelabel'+i},
                'dx':70,
                'dy': -2,
                'font-size':12,
                'fill':'#0099BE'});

            edgelabels.append('textPath')
              .attr('xlink:href',function(d,i) {return '#edgepath'+i})
              .style("pointer-events", "none")
              .text(function(d,i){return praedikate[i]});
          }

          svg.append('defs').append('marker')
            .attr({'id':'arrowhead',
              'viewBox':'-0 -5 10 10',
              'refX':25,
              'refY':0,
              //'markerUnits':'strokeWidth',
              'orient':'auto',
              'markerWidth':10,
              'markerHeight':10,
              'xoverflow':'visible'})
            .append('svg:path')
            .attr('d', 'M 0,-5 L 10 ,0 L 0,5')
            .attr('fill', '#0099BE')
            .attr('stroke','#0099BE');


          force.on("tick", function(){

            edges.attr({"x1": function(d){return d.source.x;},
              "y1": function(d){return d.source.y;},
              "x2": function(d){return d.target.x;},
              "y2": function(d){return d.target.y;}
            });

            nodes.attr({"cx":function(d){return d.x;},
              "cy":function(d){return d.y;}
            });

            nodelabels.attr("x", function(d) { return d.x; })
              .attr("y", function(d) { return d.y; });

            nodeicons.attr("x", function(d) { return d.x; })
              .attr("y", function(d) { return d.y; });

            edgepaths.attr('d', function(d) { let path='M '+d.source.x+' '+d.source.y+' L '+ d.target.x +' '+d.target.y;
              //console.log(d)
              return path});

            if (noNetworks === 1) {
              edgelabels.attr('transform',function(d,i){
                if (d.target.x<d.source.x){
                  bbox = this.getBBox();
                  rx = bbox.x+bbox.width/2;
                  ry = bbox.y+bbox.height/2;
                  return 'rotate(180 '+rx+' '+ry+')';
                }
                else {
                  return 'rotate(0)';
                }
              });
            }


            //nodes.each(collide(0.5));
          });
          //------------------------------------------------------------
          //Toggle stores whether the highlighting is on
          let toggle = 0;
          //Create an array logging what is connected to what
          let linkedByIndex = {};
          for (i = 0; i < dataset.nodes.length; i++) {
            linkedByIndex[i + "," + i] = 1;
          };
          dataset.edges.forEach(function (d) {
            linkedByIndex[d.source.index + "," + d.target.index] = 1;
          });
          //This function looks up whether a pair are neighbours
          function neighboring(a, b) {
            return linkedByIndex[a.index + "," + b.index];
          }
          function connectedNodes() {
            if (toggle == 0) {
              //Reduce the opacity of all but the neighbouring nodes
              d = d3.select(this).node().__data__;
              nodes.style("opacity", function (o) {
                return neighboring(d, o) | neighboring(o, d) ? 1 : 0.05;
              });
              edges.style("opacity", function (o) {
                return d.index == o.source.index | d.index == o.target.index ? 1 : 0.1;
              });
              //Reduce the op
              toggle = 1;
            } else {
              //Put them back to opacity=1
              nodes.style("opacity", 1);
              edges.style("opacity", 1);
              toggle = 0;
            }
          }

          //-------Collision Detection
          let padding = 1, // separation between circles
            radius=8;
          function collide(alpha) {
            let quadtree = d3.geom.quadtree(graph.nodes);
            return function(d) {
              let rb = 2*radius + padding,
                nx1 = d.x - rb,
                nx2 = d.x + rb,
                ny1 = d.y - rb,
                ny2 = d.y + rb;
              quadtree.visit(function(quad, x1, y1, x2, y2) {
                if (quad.point && (quad.point !== d)) {
                  let x = d.x - quad.point.x,
                    y = d.y - quad.point.y,
                    l = Math.sqrt(x * x + y * y);
                  if (l < rb) {
                    l = (l - rb) / l * alpha;
                    d.x -= x *= l;
                    d.y -= y *= l;
                    quad.point.x += x;
                    quad.point.y += y;
                  }
                }
                return x1 > nx2 || x2 < nx1 || y1 > ny2 || y2 < ny1;
              });
            };
          }
        }
      });
    }


    Drupal.behaviors.xnavi_network = {
      attach: function (context, settings) {
        // can access setting from 'drupalSettings';
        init();
      }
    };
    })(jQuery, Drupal, drupalSettings);
