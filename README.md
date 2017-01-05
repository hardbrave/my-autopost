## Introduction
- my-autopost是一个Wordpress插件，功能强大，支持自定义网站内容的抓取
- my-autopost是wp-autopost的简化开源版本，本人是基于一份网上广为流传的wp-autopost早期破解版本改良的，改良工作主要包括：
  - **突破限制，正常运行：** 网上破解版本并不能正常运行，并且有诸多限制（如只能开启一个任务，会在抓取的文章内容里面加上广告内容等等），my-autopost修复了这些问题，直接就能运行，并且没有任何功能限制
  - **PHP代码与HTML代码分离：** 网上破解版本PHP代码和HTML代码杂糅严重，非常不利用调试和维护，my-autopost分离了PHP逻辑代码和HTML显示代码，将HTML代码单独拆分出来，放在views目录下，方便后续维护
  - **使用Wordpress原生的表格类：** 网上破解版本使用自定义代码来实现表格显示与表格筛选等功能，my-autopost将这些自定义代码全部替换为使用Wordpress原生表格类功能实现，保持代码一致性
  - **精简代码：** 网上破解版本中存在多处重复代码和复制代码的情况，my-autopost将重复代码抽取出来，形成公共函数
- my-autopost仅为本人学习Wordpress插件开发所写，并不会用于任何商业用途，欢迎大家下载学习

## Getting started
在将代码下载或克隆到本地后，直接将整个项目（带目录）放到Wordpress网站主目录下的wp-content/plugins即可

## Demo
#### 主界面
![image](https://github.com/hardbrave/my-autopost/raw/master/snapshot/my-autopost.png)

#### 设置界面
![image](https://github.com/hardbrave/my-autopost/raw/master/snapshot/my-autopost-setting-1.png)
![image](https://github.com/hardbrave/my-autopost/raw/master/snapshot/my-autopost-setting-2.png)

#### 抓取文章界面
![image](https://github.com/hardbrave/my-autopost/raw/master/snapshot/my-autopost-article.png)
