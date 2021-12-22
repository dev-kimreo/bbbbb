<?php

namespace App\Services\ThemeBuilders;

use App\Models\EditablePages\EditablePage;
use App\Models\Solution;
use App\Models\SupportedEditablePage;
use ZipStream\Option\Archive as ZipArchive;
use ZipStream\ZipStream;

class ThemeCafe24BuilderService extends ThemeBuilderService
{
    protected function makeTunnelFile()
    {
        $raw = '
window.addEventListener("load", function(event) {
  // Tunneling Object
  window.QpickTunnel = {
    module: CAFE24API.init({
      client_id : \'pRByfNKnDaQKRevR5c8DiA\',
      version : \'2021-12-01\'
    }),

    // 쇼핑몰에 로그인 되어 있는 회원
    customer: function() {
      var CAFE24API = this.module;

      return new Promise((resolve, reject) => {
        CAFE24API.getCustomerInfo(function(err, res){
          if(err && !res.customer) {
            reject(err);
          } else {
            let dat = {
              id: res.customer.id,
              name: res.customer.name,
              nick: res.customer.nick_name
            };

            resolve(dat);
          }
        });
      });
    },

    // 장바구니 카운트
    cartCount: function() {
      var CAFE24API = this.module;

      return new Promise((resolve, reject) => {
        CAFE24API.getCartCount(function(err, res){
          err? reject(err): resolve(parseInt(res.count, 10));
        });
      });
    },

    // 최상위 카테고리 목록
    categories: function() {
      var CAFE24API = this.module;

      return new Promise((resolve, reject) => {
        CAFE24API.get(\'/api/v2/categories\', function(err, res){
          if(err) {
            reject(err)
          } else {
            let dat = [];

            for(const v of res.categories) {
              if(v.use_display != "T" || v.category_depth != 1) {
                continue;
              }

              dat.push({
                no: v.category_no,
                name: v.category_name,
                url: "/product/list.html?category_no=" + v.category_no
              });
            }

            resolve(dat);
          }
        });
      });
    },

    // 특정 상품정보
    product: function(no) {
      var CAFE24API = this.module;

      return new Promise((resolve, reject) => {
        CAFE24API.get(\'/api/v2/products/\' + no, function(err, res){
          let p = res.product;
          err? reject(err): resolve({
            no: p.product_no,
            name: p.product_name,
            image: p.list_image,
            url: "/product/detail.html?product_no=" + p.product_no,
            price: parseInt(p.price, 10),
            catchphrase: p.summary_description
          });
        });
      });
    },

    // 메인분류 상품
    mainProducts: function(group_no) {
      var instance = this;
      var CAFE24API = this.module;

      return new Promise((resolve, reject) => {
        CAFE24API.get(\'/api/v2/mains/\' + group_no + \'/products\', function(err, res){
          if(err) {
            reject(err)
          } else {
            dat = (async function(res) {
              let dat = [];

              for(const v of res.products) {
                await instance.product(v.product_no).then((res2) => {
                  dat.push(res2);
                });
              }

              resolve(dat);
            })(res);
          }
        });
      });
    },
  }
});
        ';
        $this->addFile('qpick/tunnel/tunnel.js', $raw);
    }

    protected function makeEachViewFiles()
    {
        $jHead = 'let componentRenders = {}; window.addEventListener(\'load\', (e) => {' . "\n";
        $jTail = '});';
        $jRaw = '';
        $hRaw = '<!--@layout(/qpick/layout/main.html)-->';

        foreach($this->theme->editablePages as $page) {
            $componentGroup = [
                $page->editablePageLayout->linkedHeaderComponentGroup->linkedComponents,
                $page->editablePageLayout->linkedContentComponentGroup->linkedComponents,
                $page->editablePageLayout->linkedFooterComponentGroup->linkedComponents
            ];

            foreach($componentGroup as $group) {
                foreach($group as $linkedComponent) {
                    $fileName = '/qpick/components/' . $linkedComponent->id . '.js';
                    $optJson = $this->getComponentOptionJson($linkedComponent->id);
                    $jRaw .= "/*" . $linkedComponent->component->name . "*/\n";
                    $jRaw .= 'import(\'' . $fileName . '\').then((module) => { module.setTemplate(' . $optJson . '); });' . "\n";
                    $hRaw .= '<qpick-component-' . $linkedComponent->id . '></qpick-component-' . $linkedComponent->id . '>';
                }
            }

            $this->addFile('qpick/views/' . $page->supportedEditablePage->file_name . '.js', $jHead . $jRaw . $jTail);
            $this->addFile($page->supportedEditablePage->file_name . '.html', $hRaw);
        }
    }

    protected function makeSolutionSpecializedFiles()
    {
        $raw = '
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width" />
    <title>메인 - ' . $this->theme->product->name . '</title>
    <script type="text/javascript" src="/qpick/basis/core.js"></script>
    <script type="text/javascript" src="/qpick/basis/qpick.js"></script>
    <script type="text/javascript" src="/qpick/tunnel/tunnel.js"></script>
    <script type="text/javascript" src="/qpick/views/index.js"></script>
    <link href="/qpick/basis/base.css" type="text/css" rel="stylesheet" />
  </head>
  <body id="main">

    <!--@contents-->

  </body>
</html>
        ';

        $this->addFile('qpick/layout/main.html', trim($raw));
    }
}
