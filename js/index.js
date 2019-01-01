var $$ = Dom7;

var templates = {};


Template7.registerHelper('getDlmLabel', function (name) {
  return data['_typelabel'][name];
});

Template7.registerHelper('translate', function (name) {
  return data['_strings'][name];
});

console.dir(Template7);

var app  = new Framework7({
  root: '#app',
  pushState: true,
  on: {
    init: function (page) {
	  templates.navpage = Template7.compile($$('#navpage').html());
	  templates.searchpage = Template7.compile($$('#searchpage').html());
    }
  }
});

var mainView = app.views.create('.view-main', {
  url: '/',
  stackPages: true,
  pushState: "true",
  routes: [
    {
	  path: '/search/',
	  async(routeTo, routeFrom, resolve, reject) {
			var dt = data['_search'];
			
			dt.dlms = {};
			
			app.request.json('search.php?action=getdlms&type=search&category=', function(data){
				dt.dlms = data;
				//console.log(dt);
				resolve({template: templates['searchpage'](dt)});
			});
			
			function doPage(){
				console.log(dt);
				resolve({template: templates['searchpage'](dt)});
			}
			
			return;
  	  }
	},
	{
	  path: '/dosearch/',
	  url: 'search.php?action=search'
	},
	{
	  path: '(.*)',
	  async(routeTo, routeFrom, resolve, reject) {
		console.log(routeTo, routeFrom, app.views.main.router.url);

		if(typeof routeFrom.path === "undefined"){
			resolve({ template: templates[data['main'].tpl](data['main'])});
			return;
		}
		if(!routeTo.hash)
			return;

		resolve({ template: templates[data[routeTo.hash].tpl](data[routeTo.hash])});
		//resolve({ template: templates.navpage(data[routeTo.hash])});
        //resolve({ pageName: routeTo.hash });
      }
	}
  ],
});

document.addEventListener("DOMContentLoaded", function() {

});

document.addEventListener("deviceready", appReady, false);
 function appReady(){
	 alert("ss")
 	document.addEventListener("backbutton", function(e){
 		var page = getCurrentView(app);
 		app.dialog.alert(page);
 		if(page.name=="index"){
 			navigator.notification.confirm("Are you sure want to exit?", onConfirmExit, "My Project", "Yes,No");
 		}
 		else{
 			navigator.app.backHistory();
 		}
    });
 }

 
 function onConfirmExit(button){
 	if(button==1){
 		navigator.app.exitApp();
 	}
 	else{
 		return;
 	}
 }