<?php

namespace Database\Seeders;

use App\Models\Components\Component;
use App\Models\Components\ComponentOption;
use App\Models\Components\ComponentType;
use App\Models\Components\ComponentVersion;
use App\Models\Solution;
use App\Models\Users\UserPartner;
use Illuminate\Database\Seeder;

class ComponentsAndOptionsSeeder extends Seeder
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
    <ul id="lHeaderTopMenu">
      <li><a href="#">로그인</a></li>
      <li><a href="#">회원가입</a></li>
      <li><a href="#">고객센터</a></li>
    </ul>
    <ul id="lHeaderRightMenu">
      <li class="cart"><a href="#">장바구니</a></li>
      <li class="menu"><a href="#">메뉴</a></li>
    </ul>
    <ul id="lHeaderMainMenu"></ul>
    <form>
      <input type="text" name="kw" value="" />
      <input type="submit" value="검색" />
    </form>
  </div>
</header>
            ',
            'css' => '
  header {
    height: 188px;
    margin: 0;
    padding: 0;
    border-bottom: 1px solid #e0e0e0;
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
  #lHeaderTopMenu li {
    display: inline-block;
    height: 10px;
    padding: 0 16px;
    border-right: 1px solid #e0e0e0;
    font-size: 12px;
    line-height: 12px;
  }
  #lHeaderTopMenu li:last-of-type {
    padding-right: 0;
    border-right: 0;
  }
  #lHeaderTopMenu li a {
    position: relative;
    top: -2px;
    color: #959595;
    text-decoration: none;
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
    display: inline-block;
    height: 24px;
    width: 24px;
    margin-left: 30px;
    background-color: none;
    background-size: 24px 24px;
  }
  #lHeaderRightMenu li:first-of-type {
    background-image: url("/images/ico_cart.png");
  }
  #lHeaderRightMenu li:last-of-type {
    background-image: url("/images/ico_menu.png");
  }
  #lHeaderRightMenu li a {
    overflow: hidden;
    display: inline-block;
    height: 24px;
    width: 24px;
    text-indent: -5000px;
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
    background-image: url("/images/ico_search.png");
    background-size: 24px 24px;
    text-indent: -5000px;
    cursor: pointer;
  }
            ',
            'script' => '
// Main Menu
let ul = document.querySelector("#lHeaderMainMenu");
let menuList = compOpt["menu"].split(",");

for(let i=0; i < menuList.length; i+=2) {
  let li = document.createElement("li");
  let anchor = document.createElement("a");

  anchor.href = v[i+1];
  anchor.appendChild(document.createTextNode(v[i]));

  li.appendChild(anchor);
  ul.appendChild(li);

  // Search Keyword
  // TODO - 템플릿 언어 등으로 개선 필요
  document.querySelector("input[type=text]").value = getParameterFromUrl("kw");
}
            ',
            'options' => [
                [
                    'name' => '메뉴목록',
                    'key' => 'menu',
                    'help' => '메뉴명과 URL을 쉼표(,)로 구분하여 번갈아가며 입력합니다.',
                    'default' => '구글,https://google.com,네이버,https://naver.com'
                ]
            ]
        ];

        $rawSourceCodes[] = [
            'name' => '2단 대배너',
            'html' => '
<ul id="banners"></ul>
            ',
            'css' => '
ul {
  width: 1248px;
  margin: 16px auto;
  padding: 0;
  font-size: 0;
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
}
            ',
            'script' => '
let ul = document.getElementById("banners");

for(let i=1; i<=2; i++) {
  let img = new Image();
  let li = document.createElement("li");
  let anchor = document.createElement("a");

  anchor.href = v["url" + i];
  img.src = v["img" + i];

  anchor.appendChild(img);
  li.appendChild(anchor);
  ul.appendChild(li);
}

document.querySelector("ul li").className = "selected";
            ',
            'options' => [
                [
                    'name' => '좌측배너 링크 URL',
                    'key' => 'url1',
                    'help' => '좌측배너를 클릭했을 때 표시될 링크입니다.',
                    'default' => 'https://en.wikipedia.org/wiki/Strawberry'
                ],
                [
                    'name' => '좌측배너 이미지',
                    'key' => 'img1',
                    'help' => '좌측배너에 표시할 이미지의 주소입니다.',
                    'default' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/64/Garden_strawberry_%28Fragaria_%C3%97_ananassa%29_single.jpg/750px-Garden_strawberry_%28Fragaria_%C3%97_ananassa%29_single.jpg'
                ],
                [
                    'name' => '우측배너 링크 URL',
                    'key' => 'url2',
                    'help' => '우측배너를 클릭했을 때 표시될 링크입니다.',
                    'default' => 'https://en.wikipedia.org/wiki/Cucumis_melo'
                ],
                [
                    'name' => '우측배너 이미지',
                    'key' => 'img2',
                    'help' => '우측배너에 표시할 이미지의 주소입니다.',
                    'default' => 'https://upload.wikimedia.org/wikipedia/commons/b/b0/03-05-JPN202.jpg'
                ]
            ]
        ];

        $rawSourceCodes[] = [
            'name' => '카테고리 네비게이션 바',
            'html' => '
<div id="wCategories"><ul></ul></div>
            ',
            'css' => '
#wCategories {
  height: 48px;
  border-top: 1px solid #e0e0e0;
  border-bottom: 1px solid #e0e0e0;
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
            ',
            'script' => '
let ul = document.querySelector("ul");

for(let i=1; i<=8; i++) {
  let data = v["data" + i].split(",");
  let li = document.createElement("li");
  let anchor = document.createElement("a");

  anchor.href = data[1];
  anchor.appendChild(document.createTextNode(data[0]));

  li.appendChild(anchor);
  ul.appendChild(li);
}
            ',
            'options' => [
                [
                    'name' => '1번째 카테고리',
                    'key' => 'data1',
                    'help' => '카테고리명과 URL을 쉼표(,)로 구분하여 입력합니다.',
                    'default' => '#,티셔츠'
                ],
                [
                    'name' => '2번째 카테고리',
                    'key' => 'data2',
                    'help' => '카테고리명과 URL을 쉼표(,)로 구분하여 입력합니다.',
                    'default' => '#,셔츠/블라우스'
                ],
                [
                    'name' => '3번째 카테고리',
                    'key' => 'data3',
                    'help' => '카테고리명과 URL을 쉼표(,)로 구분하여 입력합니다.',
                    'default' => '#,니트/가디건'
                ],
                [
                    'name' => '4번째 카테고리',
                    'key' => 'data4',
                    'help' => '카테고리명과 URL을 쉼표(,)로 구분하여 입력합니다.',
                    'default' => '#,원피스'
                ],
                [
                    'name' => '5번째 카테고리',
                    'key' => 'data5',
                    'help' => '카테고리명과 URL을 쉼표(,)로 구분하여 입력합니다.',
                    'default' => '#,커트/팬츠'
                ],
                [
                    'name' => '6번째 카테고리',
                    'key' => 'data6',
                    'help' => '카테고리명과 URL을 쉼표(,)로 구분하여 입력합니다.',
                    'default' => '#,아우터'
                ],
                [
                    'name' => '7번째 카테고리',
                    'key' => 'data7',
                    'help' => '카테고리명과 URL을 쉼표(,)로 구분하여 입력합니다.',
                    'default' => '#,슈즈'
                ],
                [
                    'name' => '8번째 카테고리',
                    'key' => 'data8',
                    'help' => '카테고리명과 URL을 쉼표(,)로 구분하여 입력합니다.',
                    'default' => '#,가방/지갑'
                ]
            ]
        ];

        $rawSourceCodes[] = [
            'name' => 'BEST 5',
            'html' => '
<div id="wBest">
  <h2>BEST 5</h2>
  <ul></ul>
</div>
            ',
            'css' => '
#wBest {
  width: 1248px;
  margin: 48px auto 0 auto;
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
  height: 436px;
  margin: 0;
  padding: 0;
}
li {
  position: relative;
  display: inline-block;
  width: 240px;
  height: 436px;
}
li h3 {
  position: absolute;
  top: 361px;
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
  height: 319px;
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
  top: 387px;
  left: 0;
  right: 0;
  height: 16px;
  color: #000000;
  font-size: 16px;
  line-height: 16px;
}
li .soldQty {
  position: absolute;
  top: 417px;
  left: 0;
  right: 0;
  height: 13px;
  color: #959595;
  font-size: 13px;
  line-height: 13px;
}
li .catchphrase {
  position: absolute;
  top: 335px;
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
            ',
            'script' => '
let ul = document.querySelector("ul");

for(let i=1; i<=5; i++) {
  let v = JSON.parse(compOpt["data" + i);

  let li = document.createElement("li");
  let img = new Image();
  let h3Title = document.createElement("h3");
  let divPrice = document.createElement("div");
  let divQty = document.createElement("div");
  let divPhrase = document.createElement("div");
  let anchor = document.createElement("a");

  img.src = v["img"];
  img.setAttribute("alt", v["title"]);

  h3Title.appendChild(document.createTextNode(v["title"]));

  divPrice.className = "price";
  divPrice.appendChild(document.createElement("b").appendChild(document.createTextNode(v["price"].toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","))))
  divPrice.appendChild(document.createTextNode("원"));

  divQty.className = "soldQty";
  divQty.appendChild(document.createElement("b").appendChild(document.createTextNode(v["soldQty"].toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","))))
  divQty.appendChild(document.createTextNode("개 구매"));

  divPhrase.className = "catchphrase";
  divPhrase.appendChild(document.createTextNode(v["catchphrase"]));

  anchor.href = v["url"];
  anchor.appendChild(document.createTextNode("상품보기"));

  li.appendChild(img);
  li.appendChild(h3Title);
  li.appendChild(divPrice);
  li.appendChild(divQty);
  li.appendChild(divPhrase);
  li.appendChild(anchor);
  ul.appendChild(li);
}

document.querySelector("ul li").className = "selected";
            ',
            'options' => [
                [
                    'name' => '1번째 상품 데이터',
                    'key' => 'data1',
                    'help' => '텍스트 입력란 테스트 중에는 이 란은 JSON으로 입력합니다.',
                    'default' => '{img:"/images/img_best50_1.png", url:"#", price:40000, soldQty:1294, title:"옐로우 크롬 후드 세트", catchphrase:"힙한 느낌 물씬 크롭 후드+조커팬츠", bedge:[]}'
                ],
                [
                    'name' => '2번째 상품 데이터',
                    'key' => 'data2',
                    'help' => '텍스트 입력란 테스트 중에는 이 란은 JSON으로 입력합니다.',
                    'default' => '{img:"/images/img_best50_2.png", url:"#", price:12000, soldQty:1069, title:"민트 시스루 크롭 나시", catchphrase:"선글라스도 함께 드려요", bedge:["BEST"]}'
                ],
                [
                    'name' => '3번째 상품 데이터',
                    'key' => 'data3',
                    'help' => '텍스트 입력란 테스트 중에는 이 란은 JSON으로 입력합니다.',
                    'default' => '{img:"/images/img_best50_3.png", url:"#", price:24000, soldQty:873, title:"블랙 피스 나시티", catchphrase:"힙한 강렬함", bedge:["HIP"]}'
                ],
                [
                    'name' => '4번째 상품 데이터',
                    'key' => 'data4',
                    'help' => '텍스트 입력란 테스트 중에는 이 란은 JSON으로 입력합니다.',
                    'default' => '{img:"/images/img_best50_4.png", url:"#", price:26000, soldQty:789, title:"레이스 나시 롱 원피스", catchphrase:"힙여성스러움과 섹시함이 함께", bedge:["BEST", "NEW"]}'
                ],
                [
                    'name' => '5번째 상품 데이터',
                    'key' => 'data5',
                    'help' => '텍스트 입력란 테스트 중에는 이 란은 JSON으로 입력합니다.',
                    'default' => '{img:"/images/img_best50_5.png", url:"#", price:9900, soldQty:18, title:"브이넥 나시 점프수트", catchphrase:"힙한 느낌 물씬 크롭 후드+조커팬츠", bedge:["NEW"]}'
                ]
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
                    'key' => 'url0',
                    'help' => '1번째 이미지를 클릭했을 때 표시될 링크입니다.',
                    'default' => 'https://en.wikipedia.org/wiki/Strawberry'
                ]
            ]
        ];
        */

        // DB Insert
        foreach ($rawSourceCodes as $v) {
            $version = ComponentVersion::factory()->state(
                [
                    'usable' => true,
                    'template' => $v['html'],
                    'style' => $v['css'],
                    'script' => $v['script']
                ]
            );

            foreach ($v['options'] as $opt) {
                $version = $version->has(
                    ComponentOption::factory()
                        ->state($opt)
                        ->for(ComponentType::where('code', 'text')->first(), 'type'),
                    'option'
                );
            }

            $component = Component::factory()->for(
                UserPartner::first(),
                'creator'
            )->for(
                Solution::first(),
                'solution'
            )->has(
                $version, 'version'
            )->state(
                [
                    'name' => $v['name'],
                    'first_category' => 'design',
                    'icon' => 'image'
                ]
            )->create();
        }
    }
}
