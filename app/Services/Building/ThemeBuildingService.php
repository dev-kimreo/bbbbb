<?php

namespace App\Services\Building;

use ZipStream\Option\Archive as ZipArchive;
use ZipStream\ZipStream;

abstract class ThemeBuildingService
{
    protected ZipStream $zip;

    abstract protected function makeTunnelFile();
    abstract protected function getComponentOptionJson($component_id);
    abstract protected function makeEachViewFiles();
    abstract protected function makeEachComponentFiles();
    abstract protected function makeSolutionSpecializedFiles();

    public function __construct()
    {
        // enable output of HTTP headers
        $options = new ZipArchive();
        $options->setSendHttpHeaders(true);

        // create a new zipstream object
        $this->zip = new ZipStream('qpick.zip', $options);
        $this->makeBasicFiles();
    }

    protected function makeBasicFiles()
    {
        // qpick/basis/base.css;
        $raw = 'body { margin: 0; padding: 0; }';
        $this->zip->addFile('qpick/basis/base.css', $raw);

        // qpick/basis/core.js;
        $raw = '
            window.QpickCore = {
              setTemplate: function(componentId, template, style, compOptData) {
                window.customElements.define("qpick-component-" + componentId, class extends HTMLElement {
                  constructor() {
                    super();

                    this.attachShadow({ mode: "open" });
                    this.shadowRoot.innerHTML = template + "<style>" + style + "</style>";

                    // Load Renderer
                    import("/qpick/renderers/" + componentId + ".js").then((module) => {
                      module.render(this.shadowRoot, compOptData);
                    });
                  }
                });
              }
            }
        ';
        $this->zip->addFile('qpick/basis/core.js', $raw);

        // qpick/basis/qpick.js;
        $raw = '
            window.QpickLibraries = {
              parseQueryString: function(query) {
                var vars = query.split("&");
                var query_string = {};
                for (var i = 0; i < vars.length; i++) {
                  var pair = vars[i].split("=");
                  var key = decodeURIComponent(pair[0]);
                  var value = decodeURIComponent(pair[1]);
                  // If first entry with this name
                  if (typeof query_string[key] === "undefined") {
                    query_string[key] = decodeURIComponent(value);
                    // If second entry with this name
                  } else if (typeof query_string[key] === "string") {
                    var arr = [query_string[key], decodeURIComponent(value)];
                    query_string[key] = arr;
                    // If third or later entry with this name
                  } else {
                    query_string[key].push(decodeURIComponent(value));
                  }
                }
                return query_string;
              },

              getParameterFromUrl: function(key) {
                var qs = this.parseQueryString(window.location.search.substring(1));
                return qs[key]? qs[key]: "";
              }
            }
        ';
        $this->zip->addFile('qpick/basis/qpick.js', $raw);
    }
}
