## 使用方法
在下载或克隆到本地后，直接将整个项目（带目录）放到Wordpress网站主目录下的wp-content/plugins即可

## 代码说明
- my-autopost是一个Wordpress插件，功能强大，支持自定义网站内容的抓取
- my-autopost是wp-autopost的简化开源版本，本人是基于在网上找到的一份早期的wp-autopost破解版本基础上改良的（网上广为流传的wp-autopost破解版本并不能直接使用，并且功能受限，同时PHP代码和HTML混杂在一起，不利用学习和阅读），改良部分主要包括：
  * PHP代码与HTML代码分离
  * 使用Wordpress原生的表格与表单类重写原代码自定义的表格与表单类
  * 原有代码存在多处代码复制的情况，将公用代码抽取成公用函数
  * 原有代码有诸多限制，比如，只能开启一个任务，会在抓取的文章内加水印，新代码去除了这些东西
- 此代码仅为本人学习Wordpress插件开发所写，不会用于任何商业用途


## 插件截图
#### 主界面
![image](https://github.com/hardbrave/my-autopost/raw/master/snapshot/my-autopost.png)

#### 设置界面
![image](https://github.com/hardbrave/my-autopost/raw/master/snapshot/my-autopost-setting-1.png)
![image](https://github.com/hardbrave/my-autopost/raw/master/snapshot/my-autopost-setting-2.png)

#### 抓取文章界面
![image](https://github.com/hardbrave/my-autopost/raw/master/snapshot/my-autopost-article.png)
