<?php

namespace App\Services\ThemeBuilders;

use App\Exceptions\QpickHttpException;
use App\Models\LinkedComponents\LinkedComponent;
use App\Models\Solution;
use App\Models\Themes\Theme;
use League\Flysystem\Adapter\Ftp as FtpAdapter;
use League\Flysystem\ConnectionRuntimeException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\InvalidRootException;
use ZipStream\Option\Archive as ZipArchive;
use ZipStream\ZipStream;

abstract class ThemeBuilderService implements ThemeBuilderInterface
{
    protected ZipStream $zip;
    protected Theme $theme;
    protected Solution $solution;
    protected array $linkedComponents = [];
    protected array $files = [];

    abstract protected function makeTunnelFile();

    abstract protected function makeEachViewFiles();

    abstract protected function makeSolutionSpecializedFiles();

    public function __construct()
    {
    }

    protected function addFile($path, $data)
    {
        $this->files[$path] = $data;
    }

    public function build(int $theme_id)
    {
        $this->getRelations($theme_id);
        $this->makeBasicFiles();
        $this->makeTunnelFile();
        $this->makeEachViewFiles();
        $this->makeEachComponentFiles();
        $this->makeSolutionSpecializedFiles();
    }

    public function download()
    {
        $options = new ZipArchive();
        $options->setSendHttpHeaders(true);

        $zip = new ZipStream('qpick.zip', $options);

        foreach($this->files as $path => $data) {
            $zip->addFile($path, $data);
        }

        $zip->finish();
    }

    /**
     * @throws FileNotFoundException
     * @throws QpickHttpException
     */
    public function ftpUpload(string $host, int $port, string $user, string $password, string $rootPath)
    {
        $ftp = new Filesystem(
            new FtpAdapter(
                [
                    'host' => $host,
                    'username' => $user,
                    'password' => $password,

                    /** optional config settings */
                    'port' => $port,
                    'root' => $rootPath,
                    'passive' => true,
                    'ssl' => ($port == 22),
                    'timeout' => 10,
                    'ignorePassiveAddress' => false,
                ]
            )
        );

        foreach ($this->files as $path => $data) {
            try {
                $ftp->put($path, $data);
            } catch (ConnectionRuntimeException $e) {
                if (strpos($e->getMessage(), 'Could not connect to host') !== false) {
                    throw new QpickHttpException(404, 'theme.export.ftp.host');
                } elseif (strpos($e->getMessage(), 'Could not login with connection') !== false) {
                    throw new QpickHttpException(403, 'theme.export.ftp.login');
                }
            } catch(InvalidRootException $e) {
                throw new QpickHttpException(404, 'theme.export.ftp.root');
            }
        }

        unset($ftp);
    }

    protected function getRelations(int $theme_id)
    {
        $this->theme = Theme::find($theme_id);
        $this->solution = Solution::where('name', '??????24')->first();

        foreach ($this->theme->editablePages as $page) {
            $componentGroup = [
                $page->editablePageLayout->linkedHeaderComponentGroup->linkedComponents,
                $page->editablePageLayout->linkedContentComponentGroup->linkedComponents,
                $page->editablePageLayout->linkedFooterComponentGroup->linkedComponents
            ];

            foreach ($componentGroup as $group) {
                foreach ($group as $linkedComponent) {
                    $this->linkedComponents[] = $linkedComponent;
                }
            }
        }
    }

    protected function makeBasicFiles()
    {
        // qpick/basis/base.css;
        $raw = 'body { margin: 0; padding: 0; }';
        $this->addFile('qpick/basis/base.css', $raw);

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
        $this->addFile('qpick/basis/core.js', $raw);

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
        $this->addFile('qpick/basis/qpick.js', $raw);
    }

    protected function makeEachComponentFiles()
    {
        // for Components
        $cHead = 'export function setTemplate(dat) { QpickCore.setTemplate(';
        $cTail = ', dat); };';

        // for Renderers
        $rHead = '
        export function render(shadowRoot, compOpt) {
          let arrMethod = [
            "createElement",
            "createTextNode",
            "createDocumentFragment"
          ];

          for(const fn of arrMethod) {
            shadowRoot[fn] = function(v = null){
              return document[fn](v)
            };
          }
          
          shadowRoot.innerHTML = `
        ';

        $rMiddle = '
          `;
          (function(document) {
        ';
        $rTail = '
          })(shadowRoot);
        };
        ';

        foreach ($this->linkedComponents as $linkedComponent) {
            $sourceCodes = $linkedComponent->component->usableVersion()->first();
            $raw = $linkedComponent->id . ',`' . $sourceCodes->template . '`,`' . $sourceCodes->style . '`';
            $this->addFile(
                'qpick/components/' . $linkedComponent->id . '.js',
                $cHead . $raw . $cTail
            );

            $innerHTML = $sourceCodes->template . '<style>' . $sourceCodes->style . '</style>';
            $this->addFile(
                'qpick/renderers/' . $linkedComponent->id . '.js',
                $rHead . $innerHTML . $rMiddle . $sourceCodes->script . $rTail
            );
        }
    }

    protected function getComponentOptionJson($linked_component_id)
    {
        $dat = [];
        $comp = LinkedComponent::query()->find($linked_component_id);
        $comp->linkedOptions->each(function($v) use (&$dat) {
            $dat[$v->componentOption->key] = $v->value;
        });

        return count($dat)? json_encode($dat): '{}';
    }
}
