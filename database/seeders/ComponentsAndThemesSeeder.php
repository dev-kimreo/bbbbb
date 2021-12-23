<?php

namespace Database\Seeders;

use App\Models\Components\Component;
use App\Models\Components\ComponentOption;
use App\Models\Components\ComponentOptionProperty;
use App\Models\Components\ComponentType;
use App\Models\Components\ComponentVersion;
use App\Models\EditablePages\EditablePage;
use App\Models\EditablePages\EditablePageLayout;
use App\Models\LinkedComponents\LinkedComponent;
use App\Models\LinkedComponents\LinkedComponentGroup;
use App\Models\LinkedComponents\LinkedComponentOption;
use App\Models\Solution;
use App\Models\SupportedEditablePage;
use App\Models\Themes\Theme;
use App\Models\Themes\ThemeProduct;
use App\Models\Users\UserPartner;
use Illuminate\Database\Seeder;

class ComponentsAndThemesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rawSourceCodes = [];

        $rawSourceCodes[] = [
            'name' => '화이트 컨셉트 헤더',
            'html' => '
<header>
  <div id="lHeaderWrap">
    <h1><a href="/">LOGO</a></h1>
    <div id="lHeaderTopMenu">
      <span data-qpick-element="user-name"></span>
      <a data-qpick-element="login">로그인</a>
      <a data-qpick-element="user-reg">회원가입</a>
      <a data-qpick-element="user-info">회원정보수정</a>
      <a data-qpick-element="logout">로그아웃</a>
      <a data-qpick-element="customer-service">게시판</a>
    </div>
    <ul id="lHeaderRightMenu">
      <li class="cart"><a data-qpick-element="cart">장바구니</a><span data-qpick-element="cart-count"></span></li>
      <li class="menu"><a href="#">메뉴</a></li>
    </ul>
    <ul id="lHeaderMainMenu"></ul>
    <form data-qpick-form="search">
      <input type="text" data-qpick-input="search-keyword" />
      <input type="submit" value="검색" />
    </form>
  </div>
</header>
            ',
            'css' => '
/* Desktop Device */
@media (min-width: 1025px) {
  header {
    height: 188px;
    margin: 0;
    padding: 0;
    border-bottom: 1px solid #e0e0e0;
    font-family: sans-serif;
  }
  #lHeaderWrap {
    position: relative;
    width: 1248px;
    height: 188px;
    margin: auto;
    padding: 0;
  }
  h1 {
    position: absolute;
    top: 71px;
    left : 0;
    height: 46px;
    margin: 0;
    padding: 0;
    font-size: 40px;
    line-height: 46px;
  }
  h1 a {
    color: #000000;
    text-decoration: none;
  }
  #lHeaderTopMenu {
    position: absolute;
    top: 23px;
    right: 0;
    height: 12px;
    margin: 0;
    padding: 0;
  }
  #lHeaderTopMenu a,
  #lHeaderTopMenu span {
    display: inline-block;
    height: 10px;
    padding: 0 16px;
    border-right: 1px solid #e0e0e0;
    font-size: 12px;
    line-height: 12px;
    text-decoration: none;
  }
  #lHeaderTopMenu span {
    color: #333;
    font-weight: 700;
  }
  #lHeaderTopMenu a {
    color: #959595;
  }
  #lHeaderTopMenu a:last-of-type {
    padding-right: 0;
    border-right: 0;
  }
  #lHeaderRightMenu {
    position: absolute;
    top: 82px;
    right: 0;
    height: 24px;
    margin: 0;
    padding: 0;
  }
  #lHeaderRightMenu li {
    position: relative;
    display: inline-block;
    height: 24px;
    width: 24px;
    margin-left: 30px;
    background-color: none;
    background-size: 24px 24px;
  }
  #lHeaderRightMenu li:first-of-type {
    background-image: url("https://raphanus.cafe24.com/cocen_images/ico_cart.png");
  }
  #lHeaderRightMenu li:last-of-type {
    background-image: url("https://raphanus.cafe24.com/cocen_images/ico_menu.png");
  }
  #lHeaderRightMenu li a {
    overflow: hidden;
    display: inline-block;
    height: 24px;
    width: 24px;
    text-indent: -5000px;
  }
  #lHeaderRightMenu li span {
    position: absolute;
    top: -4px;
    right: -2px;
    display: inline-block;
    height: 10px;
    padding: 2px 3px;
    background-color: #e74c3c;
    font-size: 10px;
    color: #fff;
    font-weight: 700;
    line-height: 10px;
    border-radius: 3px;
  }
  #lHeaderMainMenu {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 16px;
    margin: 0;
    padding: 22px 0;
  }
  #lHeaderMainMenu li {
    display: inline-block;
    height: 16px;
    margin-right: 32px;
    font-size: 15px;
    line-height: 16px;
  }
  #lHeaderMainMenu li a {
    color: #2f2f2f;
    text-decoration: none;
  }
  form {
    position: absolute;
    top: 70px;
    left: 740px;
    width: 400px;
    height: 48px;
  }
  form input[type="text"] {
    width: 368px;
    height: 16px;
    padding: 16px;
    border: 0;
    border-radius: 24px;
    background-color: #f5f5f5;
    font-size: 16px;
    line-height: 16px;
  }
  form input[type="submit"] {
    overflow: hidden;
    position: absolute;
    right: 18px;
    top: 12px;
    width: 24px;
    height: 24px;
    border: 0;
    background-color: #f5f5f5;
    background-image: url("https://raphanus.cafe24.com/cocen_images/ico_search.png");
    background-size: 24px 24px;
    text-indent: -5000px;
    cursor: pointer;
  }
}
/* Mobile Device */
@media (max-width: 1024px) {
  header {
    position: relative;
    height: 100px;
    margin: 0;
    padding: 0;
    font-family: sans-serif;
  }
  h1 {
    position: absolute;
    top: 16px;
    left: 16px;
    margin: 0;
    padding: 0;
    font-size: 21px;
    line-height: 21px;
    letter-spacing: -0.03em;
  }
  h1 a {
    color: #000000;
    text-decoration: none;
  }
  ul {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 0;
    font-size: 15px;
  }
  ul li {
    display: inline-block;
    padding: 14px 12px;
  }
  #lHeaderTopMenu {
    display: none;
    position: absolute;
    left: 100%;
    bottom: 0;
    width: 450px;
    background: #fff;
    margin: 0;
    padding: 0;
  }
  #lHeaderRightMenu {
    position: absolute;
    top: 17px;
    right: 10px;
    height: 24px;
    margin: 0;
    padding: 0;
    text-align: right;
  }
  #lHeaderRightMenu li {
    position: relative;
    display: inline-block;
    height: 20px;
    width: 20px;
    margin-left: 20px;
    padding: 0;
    background-color: none;
    background-size: 20px 20px;
    background-position: center center;
    background-repeat: no-repeat;
  }
  #lHeaderRightMenu li:first-of-type {
    background-image: url("https://raphanus.cafe24.com/cocen_images/ico_cart.png");
  }
  #lHeaderRightMenu li:last-of-type {
    background-image: url("https://raphanus.cafe24.com/cocen_images/ico_menu.png");
  }
  #lHeaderRightMenu li a {
    display: block;
    overflow: hidden;
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    height: 24px;
    width: 24px;
    text-indent: 5000px;
  }
  #lHeaderRightMenu li span {
    position: absolute;
    top: -4px;
    right: -2px;
    display: inline-block;
    height: 10px;
    padding: 2px 3px;
    background-color: #e74c3c;
    font-size: 10px;
    color: #fff;
    font-weight: 700;
    line-height: 10px;
    border-radius: 3px;
  }
  #lHeaderMainMenu {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 40px;
    margin: 0;
    padding: 0 8px;
  }
  #lHeaderMainMenu li {
    display: inline-block;
    height: 16px;
    padding: 12px 12px;
    font-size: 15px;
    line-height: 16px;
  }
  #lHeaderMainMenu li a {
    color: #2f2f2f;
    text-decoration: none;
  }
  form {
    position: absolute;
    top: 12px;
    left: 117px;
    right: 95px;
    height: 32px;
  }
  form input[type="text"] {
    position: absolute;
    width: 100%;
    height: 32px;
    padding: 0 16px;
    border: 0;
    border-radius: 16px;
    box-sizing: border-box;
    background-color: #f5f5f5;
    font-size: 16px;
    line-height: 16px;
  }
  form input[type="submit"] {
    overflow: hidden;
    position: absolute;
    right: 8px;
    top: 0;
    width: 30px;
    height: 30px;
    margin: 0;
    border: 0;
    background-color: transparent;
    background-image: url("https://raphanus.cafe24.com/cocen_images/ico_search.png");
    background-size: 20px 20px;
    background-position: center center;
    background-repeat: no-repeat;
    text-indent: -5000px;
    cursor: pointer;
  }
}
            ',
            'script' => '
// Data postprocessing for the theme editor
let menu = []; 
try {
  menu = JSON.parse(compOpt[\'menu\'].text);
} catch(e) {
  menu = [];
}

// Main Menu
let ul = document.querySelector("#lHeaderMainMenu");

for(const v of menu) {
  let li = document.createElement("li");
  let anchor = document.createElement("a");

  anchor.href = v["url"];
  anchor.appendChild(document.createTextNode(v["title"]));

  li.appendChild(anchor);
  ul.appendChild(li);

  // Links
  document.querySelector("[data-qpick-element=\'cart\']").setAttribute("href", "/order/basket.html");
  document.querySelector("[data-qpick-element=\'customer-service\']").setAttribute("href", "/board/index.html");


  // Search Form
  document.querySelector("[data-qpick-form=\'search\']").setAttribute("action", "/product/search.html");
  document.querySelector("[data-qpick-input=\'search-keyword\']").setAttribute("name", "keyword");

  // Link logged on user
  QpickTunnel.customer().then((res) => {
    document.querySelector("[data-qpick-element=\'user-name\']").innerText = res.name;
    document.querySelector("[data-qpick-element=\'login\']").style.display = "none";
    document.querySelector("[data-qpick-element=\'user-reg\']").style.display = "none";
    document.querySelector("[data-qpick-element=\'user-info\']").setAttribute("href", "/member/modify.html");
    document.querySelector("[data-qpick-element=\'logout\']").setAttribute("href", "/exec/front/Member/logout/");
  }).catch((err) => {
    document.querySelector("[data-qpick-element=\'user-name\']").style.display = "none";
    document.querySelector("[data-qpick-element=\'login\']").setAttribute("href", "/member/login.html");
    document.querySelector("[data-qpick-element=\'user-reg\']").setAttribute("href", "/member/join.html");
    document.querySelector("[data-qpick-element=\'user-info\']").style.display = "none";
    document.querySelector("[data-qpick-element=\'logout\']").style.display = "none";
  });

  // Cart count
  QpickTunnel.cartCount().then((res) => {
    let o = document.querySelector("[data-qpick-element=\'cart-count\']");
    (res > 0)? (o.innerText = res): (o.style.visibility = "hidden");
  }).catch((err) => {
    let o = document.querySelector("[data-qpick-element=\'cart-count\']");
    o.style.visibility = "hidden";
  });

  // Search Keyword
  // TODO - 템플릿 언어 등으로 개선 필요
  document.querySelector("input[type=text]").value = QpickLibraries.getParameterFromUrl("kw");
}
            ',
            'options' => [
                [
                    'name' => '메뉴목록',
                    'type' => 'Text Field',
                    'key' => 'menu',
                    'help' => '다음과 같은 형식의 JSON 배열로 입력합니다: [{title:"", url:""},{title:"", url:""}]',
                    'default' => '[{"title":"신상NEW","url":"/"},{"title":"BEST50","url":"/"}]'
                ],
                /*
                 *  데이터 형태
                 *  {
                 *      "menu":[
                 *          {"title":'신상NEW',"url":'/'},
                 *          {"title":'BEST50',"url":'/'},
                 *          {"title":'자체제작',"url":'/'},
                 *          {"title":'썸머바캉스',"url":'/'}
                 *      ]
                 *  }
                 */
            ]
        ];

        $rawSourceCodes[] = [
            'name' => '2단 대배너',
            'html' => '<ul id="banners"></ul>',
            'css' => '
/* Desktop Device */
@media (min-width: 1025px) {
  ul {
    width: 1248px;
    margin: 16px auto;
    padding: 0;
    font-size: 0;
    font-family: sans-serif;
  }
  li {
    display: inline-block;
    margin-right: 16px;
  }
  li:last-of-type {
    margin-right: 0;
  }
  img {
    width: 616px;
    height: 461px;
    border-radius: 16px;
  }
}
/* Mobile Device */
@media (max-width: 1024px) {
  ul {
    overflow: hidden;
    width: 100%;
    padding: 0;
    font-size: 0;
    font-family: sans-serif;
  }
  li {
    display: none;
  }
  li:first-child {
    display: list-item;
  }
  img {
    width: 100%;
  }
}
            ',
            'script' => '
// Data postprocessing for the theme editor
let banners
try {
  banners = JSON.parse(compOpt[\'banners\'].text);
} catch(e) {
  banners = [];
}

let ul = document.getElementById("banners");

for(const v of banners) {
  let img = new Image();
  let li = document.createElement("li");
  let anchor = document.createElement("a");

  anchor.href = v["url"];
  img.src = v["img"];

  anchor.appendChild(img);
  li.appendChild(anchor);
  ul.appendChild(li);
}
            ',
            'options' => [
                [
                    'name' => '배너정보',
                    'type' => 'Text Field',
                    'key' => 'banners',
                    'help' => '다음과 같은 형식의 JSON 배열로 2개의 배너를 입력합니다: [{img:"", url:""},{img:"", url:""}]',
                    'default' => '[{"img":"https://raphanus.cafe24.com/cocen_images/img_mainbnr_1.png","url":"/product.html"},{"img":"https://raphanus.cafe24.com/cocen_images/img_mainbnr_2.png","url":"/product.html"}]'
                ],
                /*
                 *  데이터 형태
                 *  {
                 *  "banners":[
                 *      {"img":'https://raphanus.cafe24.com/cocen_images/img_mainbnr_1.png',"url":'/product.html'},
                 *      {"img":'https://raphanus.cafe24.com/cocen_images/img_mainbnr_2.png',"url":'/product.html'}
                 *  ]
                 *  }
                */
            ]
        ];

        $rawSourceCodes[] = [
            'name' => '카테고리 네비게이션 바',
            'html' => '<div id="wCategories"><ul></ul></div>',
            'css' => '
/* Desktop Device */
@media (min-width: 1025px) {
  #wCategories {
    height: 48px;
    border-top: 1px solid #e0e0e0;
    border-bottom: 1px solid #e0e0e0;
    font-family: sans-serif;
  }
  ul {
    width: 1248px;
    margin: 16px auto;
    padding: 0;
    font-size: 0;
    text-align: center;
  }
  li {
    display: inline-block;
    margin: 0 22px;
    font-size: 15px;
    line-height: 16px;
  }
  li a {
    color: #606060;
    text-decoration: none;
  }
}
/* Mobile Device */
@media (max-width: 1024px) {
  #wCategories {
    background-color: #ececec;
    padding: 24px 17px;
    font-family: sans-serif;
  }
  ul {
    display: grid;
    margin: 0;
    padding: 0;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;

  }
  li {
    display: inline-block;
    padding: 0;
    background-color: #fff;
    text-align: center;
  }
  li a {
    display: inline-block;
    height: 15px;
    padding: 12px;
    color: #606060;
    font-size: 15px;
    text-decoration: none;
    line-height: 15px;
  }
}
            ',
            'script' => '
let ul = document.querySelector("ul");

QpickTunnel.categories().then((res) => {
  for(const v of res) {
    let li = document.createElement("li");
    let anchor = document.createElement("a");

    anchor.href = v.url;
    anchor.appendChild(document.createTextNode(v.name));

    li.appendChild(anchor);
    ul.appendChild(li);
  }
});
            ',
            'options' => []
        ];

        $rawSourceCodes[] = [
            'name' => 'MD`s Pick',
            'html' => '<div id="wBest"><h2></h2><ul></ul></div>',
            'css' => '
/* Desktop Device */
@media (min-width: 1025px) {
  #wBest {
    width: 1248px;
    margin: 48px auto 0 auto;
    font-family: sans-serif;
  }
  h2 {
    height: 22px;
    margin: 0;
    padding: 24px 0;
    font-size: 22px;
    font-weight: bold;
    line-height: 22px;
    text-align: center;
  }
  ul {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    height: 357px;
    margin: 0;
    padding: 0;
  }
  li {
    position: relative;
    display: inline-block;
    width: 240px;
    height: 357px;
  }
  li h3 {
    overflow: hidden;
    position: absolute;
    top: 282px;
    left: 0;
    right: 0;
    height: 16px;
    margin: 0;
    padding: 0;
    color: #000000;
    font-size: 16px;
    font-weight: bold;
    line-height: 16px;
  }
  li img {
    width: 240px;
    height: 240px;
  }
  li a {
    overflow: hidden;
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    text-indent: -5000px;
  }
  li .price {
    position: absolute;
    top: 308px;
    left: 0;
    right: 0;
    height: 16px;
    color: #000000;
    font-size: 16px;
    line-height: 16px;
  }
  li .soldQty {
    position: absolute;
    top: 338px;
    left: 0;
    right: 0;
    height: 13px;
    color: #959595;
    font-size: 13px;
    line-height: 13px;
  }
  li .catchphrase {
    position: absolute;
    top: 256px;
    left: 0;
    right: 0;
    height: 13px;
    color: #959595;
    font-size: 13px;
    line-height: 13px;
    letter-spacing: -1px;
  }
  li .catchphrase b,
  li .catchphrase i {
    display: inline-block;
    height: 10px;
    padding: 3px 8px;
    margin-right: 4px;
    font-size: 9px;
    font-style: normal;
    font-weight: normal;
    line-height: 10px;
    letter-spacing: 0;
  }
  li .catchphrase b {
    color: #F55555;
    border: 1px solid #F55555;
  }
  li .catchphrase i {
    color: #ffffff;
    border: 1px solid #43C7FF;
    background-color: #43C7FF;
  }
}
/* Mobile Device */
@media (max-width: 1024px) {
  #wBest {
    width: 100%;
    font-family: sans-serif;
    overflow-x: scroll;
    overflow-y: hidden;
  }
  h2 {
    height: 22px;
    margin: 0;
    padding: 24px 0;
    font-size: 22px;
    font-weight: bold;
    line-height: 22px;
    text-align: center;
  }
  ul {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    width: calc(200vw + 48px);
    margin: 0;
    padding: 0;
  }
  li {
    position: relative;
    display: inline-block;
    width: 40vw;
  }
  li a {
    overflow: hidden;
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    text-indent: -5000px;
  }
  li img {
    width: 100%;
    margin-bottom: 103px;
  }
  li h3 {
    position: absolute;
    bottom: 48px;
    left: 0;
    right: 0;
    height: 14px;
    margin: 0;
    padding: 0;
    color: #000000;
    font-size: 14px;
    font-weight: bold;
    line-height: 14px;
    letter-spacing: -1px;
    word-spacing: -1px;
  }
  li .price {
    position: absolute;
    bottom: 24px;
    left: 0;
    right: 0;
    height: 14px;
    color: #000000;
    font-size: 14px;
    line-height: 14px;
  }
  li .soldQty {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 12px;
    color: #959595;
    font-size: 12px;
    line-height: 12px;
  }
  li .catchphrase {
    overflow: hidden;
    position: absolute;
    bottom: 71px;
    left: 0;
    right: 0;
    height: 11px;
    color: #959595;
    font-size: 11px;
    line-height: 11px;
    letter-spacing: -1px;
  }
  li .catchphrase b,
  li .catchphrase i {
    display: inline-block;
    height: 10px;
    padding: 3px 8px;
    margin-right: 4px;
    font-size: 9px;
    font-style: normal;
    font-weight: normal;
    line-height: 10px;
    letter-spacing: 0;
  }
  li .catchphrase b {
    color: #F55555;
    border: 1px solid #F55555;
  }
  li .catchphrase i {
    color: #ffffff;
    border: 1px solid #43C7FF;
    background-color: #43C7FF;
  }
}
            ',
            'script' => '
let ul = document.querySelector("ul");

document.querySelector("h2").innerText = compOpt["title"].text;

QpickTunnel.mainProducts(compOpt["groupNo"].text).then((res) => {
  for(const v of res) {
    let li = document.createElement("li");
    let img = new Image();
    let h3Title = document.createElement("h3");
    let divPrice = document.createElement("div");
    let divOrgPrice = document.createElement("div");
    let divSellPrice = document.createElement("div");
    let divPhrase = document.createElement("div");
    let anchor = document.createElement("a");

    img.src = v.image;
    img.setAttribute("alt", v.name);

    h3Title.appendChild(document.createTextNode(v.name));

    divPrice.className = "price";

    if(v.orgPrice) {
      divOrgPrice.className = "orgPrice";
      divOrgPrice.appendChild(document.createElement("b").appendChild(document.createTextNode(v.orgPrice.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","))))
      divOrgPrice.appendChild(document.createTextNode("원"));
    }

    divSellPrice.className = "sellPrice";
    divSellPrice.appendChild(document.createElement("b").appendChild(document.createTextNode(v.price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","))))
    divSellPrice.appendChild(document.createTextNode("원"));

    divPhrase.className = "catchphrase";
    divPhrase.appendChild(document.createTextNode(v.catchphrase));

    anchor.href = v.url;
    anchor.appendChild(document.createTextNode("상품보기"));

    li.appendChild(img);
    li.appendChild(h3Title);
    divPrice.appendChild(divSellPrice);
    divPrice.appendChild(divOrgPrice);
    li.appendChild(divPrice);
    li.appendChild(divPhrase);
    li.appendChild(anchor);
    ul.appendChild(li);
  }
});
            ',
            'options' => [
                [
                    'name' => '타이틀',
                    'type' => 'Text Field',
                    'key' => 'title',
                    'help' => '영역 상단에 표시될 타이틀입니다.',
                    'default' => 'MD`s PICK'
                ],
                [
                    'name' => '메인진열분류번호',
                    'type' => 'Text Field',
                    'key' => 'groupNo',
                    'help' => '노출할 메인진열분류의 번호를 입력합니다. 1개만 입력할 수 있습니다.',
                    'default' => '2'
                ]
                /*
                 *  데이터 형태
                 *  {
                 *      "title": "MD`s PICK",
                 *      "groupNo": 2
                 *  }
                */
            ]
        ];

        $rawSourceCodes[] = [
            'name' => '4단 배너구성',
            'html' => '<div id="wNewDeal"><h2></h2><ul></ul></div>',
            'css' => '
/* Desktop Device */
@media (min-width: 1025px) {
  #wNewDeal {
    width: 1248px;
    margin: 48px auto 0 auto;
    font-family: sans-serif;
  }
  h2 {
    height: 22px;
    margin: 0;
    padding: 24px 0;
    font-size: 22px;
    font-weight: bold;
    line-height: 22px;
    text-align: center;
  }
  ul {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    justify-items: stretch;
    margin: 0;
    padding: 0;
  }
  li {
    position: relative;
    display: inline-block;
    width: 312;
    height: 540px;
  }
  li h3 {
    position: absolute;
    top: 458px;
    left: 0;
    right: 0;
    height: 16px;
    margin: 0;
    padding: 0;
    color: #000000;
    font-size: 16px;
    font-weight: bold;
    line-height: 16px;
  }
  li img {
    width: 312px;
    height: 415px;
  }
  li a {
    overflow: hidden;
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    text-indent: -5000px;
  }
  li .price {
    position: absolute;
    top: 487px;
    left: 0;
    right: 0;
    height: 16px;
  }
  li .price div {
    display: inline-block;
  }
  li .price .sellPrice {
    margin-right: 12px;
    color: #000000;
    font-size: 16px;
    font-weight: bold;
    line-height: 16px;
    letter-spacing: -1px;
  }
  li .price .orgPrice
  {
    color: #AAAAAA;
    font-weight: bold;
    font-size: 13px;
    text-decoration: line-through;
    line-height: 15px;
    letter-spacing: -1px;;
  }
  li .catchphrase {
    position: absolute;
    top: 430px;
    left: 0;
    right: 0;
    height: 13px;
    color: #959595;
    font-size: 13px;
    line-height: 13px;
    letter-spacing: -1px;
  }
  li .catchphrase b,
  li .catchphrase i {
    display: inline-block;
    height: 10px;
    padding: 3px 8px;
    margin-right: 4px;
    font-size: 9px;
    font-style: normal;
    font-weight: normal;
    line-height: 10px;
    letter-spacing: 0;
  }
  li .catchphrase b {
    color: #F55555;
    border: 1px solid #F55555;
  }
  li .catchphrase i {
    color: #ffffff;
    border: 1px solid #43C7FF;
    background-color: #43C7FF;
  }
}
/* Mobile Device */
@media (max-width: 1024px) {
  h2 {
    height: 22px;
    margin: 0;
    padding: 30px 0;
    font-size: 22px;
    font-weight: bold;
    line-height: 22px;
    text-align: center;
  }
  ul {
    margin: 0;
    padding: 0;
  }
  li {
    position: relative;
    display: inline-block;
    margin: 0;
    padding: 0;
    width: 50%;
  }
  li a {
    overflow: hidden;
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    text-indent: -5000px;
  }
  li img {
    width: 100%;
    margin-bottom: 127px;
  }
  li h3 {
    position: absolute;
    left: 16px;
    right: 16px;
    bottom: 56px;
    height: 32px;
    margin: 0;
    padding: 0;
    color: #000000;
    font-size: 14px;
    font-weight: bold;
    line-height: 14px;
    letter-spacing: -1px;
  }
  li .price {
    position: absolute;
    left: 16px;
    right: 16px;
    bottom: 32px;
    height: 14px;
  }
  li .sellPrice {
    display: inline;
    font-weight: bold;
    font-size: 14px;
    line-height: 14px;
    letter-spacing: -1px;
  }
  li .orgPrice {
    display: inline;
    margin-left: 6px;
    color: #AAAAAA;
    font-size: 13px;
    text-decoration: line-through;
    line-height: 15px;
    letter-spacing: -1px;;
  }
  li .catchphrase {
    position: absolute;
    left: 16px;
    right: 16px;
    bottom: 95px;
    height :11px;
    color: #959595;
    font-size: 11px;
    line-height: 11px;
    letter-spacing: -1px;
  }
}
            ',
            'script' => '
// Data postprocessing for the theme editor
let items;
try {
  items = JSON.parse(\'[\' + compOpt[\'items\'].text + \']\');
} catch(e) {
  items = [];
}

let ul = document.querySelector("ul");

document.querySelector("h2").innerText = compOpt["title"].text;

for(const no of items) {
  let li = document.createElement("li");
  ul.appendChild(li);

  QpickTunnel.product(no).then((res) => {
    let img = new Image();
    let h3Title = document.createElement("h3");
    let divPrice = document.createElement("div");
    //let divQty = document.createElement("div");
    let divPhrase = document.createElement("div");
    let anchor = document.createElement("a");

    img.src = res.image;
    img.setAttribute("alt", res.name);

    h3Title.appendChild(document.createTextNode(res.name));

    let price_formatted = parseInt(res.price).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    divPrice.className = "price";
    divPrice.appendChild(document.createElement("b").appendChild(document.createTextNode(price_formatted)))
    divPrice.appendChild(document.createTextNode("원"));

    //divQty.className = "soldQty";
    //divQty.appendChild(document.createElement("b").appendChild(document.createTextNode(res.soldQty.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","))))
    //divQty.appendChild(document.createTextNode("개 구매"));

    divPhrase.className = "catchphrase";
    divPhrase.appendChild(document.createTextNode(res.catchphrase));

    anchor.href = res.url;
    anchor.appendChild(document.createTextNode("상품보기"));

    li.appendChild(img);
    li.appendChild(h3Title);
    li.appendChild(divPrice);
    //li.appendChild(divQty);
    li.appendChild(divPhrase);
    li.appendChild(anchor);
  });
}
            ',
            'options' => [
                [
                    'name' => '타이틀',
                    'type' => 'Text Field',
                    'key' => 'title',
                    'help' => '영역 상단에 표시될 타이틀입니다.',
                    'default' => 'NEW DEAL'
                ],
                [
                    'name' => '상품번호',
                    'type' => 'Text Field',
                    'key' => 'items',
                    'help' => '노출할 상품번호를 입력합니다. 여러 개를 입력할 경우 쉼표(,)로 연결하여 입력합니다.',
                    'default' => '9,10,11,12,13'
                ]
                /*
                 *  데이터 형태
                 *  {
                 *      "title": "NEW DEAL",
                 *      "items": [9, 10, 11, 12, 13]
                 *  }
                */
            ]
        ];

        /*
        $rawSourceCodes[] = [
            'name' => '컴포넌트 옵션 이름',
            'html' => '',
            'css' => '',
            'script' => '',
            'options' => [
                [
                    'name' => '1번째 링크 URL',
                    'type' => 'Text Field',
                    'key' => 'url0',
                    'help' => '1번째 이미지를 클릭했을 때 표시될 링크입니다.',
                    'default' => 'https://en.wikipedia.org/wiki/Strawberry'
                ]
            ]
        ];
        */

        // 파트너 회원
        $userPartner = UserPartner::query()->first();

        // 솔루션
        $solution = Solution::query()->where('name', '카페24')->first();

        // 컴포넌트
        $component = [];
        foreach ($rawSourceCodes as $v) {
            $component[] = $currComponent = Component::query()->create(
                [
                    'user_partner_id' => $userPartner->id,
                    'solution_id' => $solution->id,
                    'name' => $v['name'],
                    'use_other_than_maker' => 1,
                    'first_category' => 'design',
                    'use_blank' => 0,
                    'use_all_page' => 1,
                    'icon' => 'image',
                    'display' => 1,
                    'status' => 'registered'
                ]
            );

            // 컴포넌트 버전
            $version = ComponentVersion::query()->create(
                [
                    'component_id' => $currComponent->id,
                    'usable' => 1,
                    'template' => $v['html'],
                    'style' => $v['css'],
                    'script' => $v['script']
                ]
            );

            // 컴포넌트 옵션
            foreach ($v['options'] as $opt) {
                $typeId = ComponentType::query()->where('name', $opt['type'])->first()->id;
                $compOpt = ComponentOption::query()->create(
                    [
                        'component_version_id' => $version->id,
                        'component_type_id' => $typeId,
                        'name' => $opt['name'],
                        'key' => $opt['key'],
                        'help' => $opt['help'],
                        'display_on_pc' => true,
                        'display_on_mobile' => true,
                        'hideable' => false,
                        'attributes' => '["textMaxLength"]'
                    ]
                );

                ComponentOptionProperty::query()->create(
                    [
                        'component_option_id' => $compOpt->id,
                        'component_type_property_id' => $typeId,
                        'key' => 'text',
                        'name' => $opt['name'],
                        'initial_value' => $opt['default']
                    ]
                );
            }
        }

        // 테마상품
        $themeProduct = ThemeProduct::query()->create(
            [
                'user_partner_id' => $userPartner->id,
                'name' => '패션/어패럴 화이트 테마',
                'all_usable' => 1
            ]
        );

        // 테마
        $theme = Theme::query()->create(
            [
                'theme_product_id' => $themeProduct->id,
                'solution_id' => $solution->id,
                'status' => 'registered',
                'display' => 1
            ]
        );

        // 지원가능 에디터 지원 페이지
        $supportedEditablePage = SupportedEditablePage::query()
            ->where('solution_id', $solution->id)
            ->first();

        // 에디터 지원 페이지
        $editablePage = EditablePage::query()->create(
            [
                'theme_id' => $theme->id,
                'supported_editable_page_id' => $supportedEditablePage->id,
                'name' => $supportedEditablePage->name
            ]
        );

        // 연동 컴포넌트 그룹
        $header = LinkedComponentGroup::query()->create();
        $contents = LinkedComponentGroup::query()->create();
        $footer = LinkedComponentGroup::query()->create();

        // 에디터 지원 페이지 레이아웃
        $layout = EditablePageLayout::query()->create(
            [
                'editable_page_id' => $editablePage->id,
                'header_component_group_id' => $header->id,
                'content_component_group_id' => $contents->id,
                'footer_component_group_id' => $footer->id,
            ]
        );

        // 연동 컴포넌트
        foreach ($component as $k => $v) {
            $groupId = $k == 0 ? $header->id : $contents->id;

            $linkedComponent = LinkedComponent::query()->create(
                [
                    'linked_component_group_id' => $groupId,
                    'component_id' => $v->id,
                    'name' => $v->name,
                    'display_on_pc' => 1,
                    'display_on_mobile' => 1,
                    'sort' => 999
                ]
            );

            // 연동 컴포넌트 옵션
            $v->version->first()->option->each(function ($v2) use ($v, $linkedComponent) {
                $values = [];
                $v2->properties->each(function ($v3) use (&$values) {
                    $values[$v3->key] = $v3->initial_value;
                });

                LinkedComponentOption::query()->create(
                    [
                        'component_option_id' => $v2->id,
                        'linked_component_id' => $linkedComponent->id,
                        'value' => $values
                    ]
                );
            });
        }
    }
}
