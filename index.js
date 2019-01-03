var $$ = Dom7;

var templates = {};


Template7.registerHelper('getDlmLabel', function (name) {
  return data['_typelabel'][name];
});

Template7.registerHelper('getTarget', function (name) {
	if(name.substring(0,1) != '/')
		return '#'+name;
	return name;
});

Template7.registerHelper('translate', function (name) {
  return (data['_strings'] && data['_strings'][name]) ? data['_strings'][name] : name;
});


var app  = new Framework7({
  root: '#app',
  pushState: true,
  panel: {
    swipe: 'left',
    swipeActiveArea: 50,
  },
  on: {
    init: function () {
	  templates.navpage = Template7.compile($$('#navpage').html());
	  templates.searchpage = Template7.compile($$('#searchpage').html());
	  templates.navbrowse = Template7.compile($$('#navbrowse').html());
	  Template7.registerPartial('navbar', $$('#navbar').html());
	  
	  return;
	  var stateObj = { quit: true };
	  //window.history.replaceState({ quit: true }, null, document.url);
	  window.history.pushState(null, null, document.url);
	  	  
	  window.onpopstate = function(event) {
		  //console.log(event, window.history.state);
		  //alert(window.history.length);
		  //return;
		  if(!window.history.state)
			  window.history.pushState(null, null, document.url);
		  
		  event.preventDefault();
		  return;
		  if(Object.keys(window.history.state).length == 1){
				toastCenter = app.toast.create({
				  text: 'Press Back again to exit Application',
				  position: 'center',
				  closeTimeout: 2000,
				}).open();
			  //quit = true;
			  //window.history.pushState(null, null, document.URL);
		  }
		  else if(window.history.state == null){
			window.history.replaceState({ quit: true }, null, document.url);
			//window.history.pushState(null, null, document.url);			  
		  }
		};
		
	  window.onhashchange = function(event){
		  console.log(event);
	  }
    },
	formAjaxSuccess: function (formEl, data, xhr) {
		console.dir(arguments);
		if($$(formEl).hasClass('searchform'))
			console.log('ok');
	},
	
  }
});


$$(document).on('pageBeforeInit', '[data-page="smart-select-popup"]', function (e) {
    var page = e.detail.page;
	console.log(page);
    // Add not found relation
    $$(page.container).find('.searchbar').attr('data-searchbar-not-found', '.searchbar-not-found');
    // Add link to add new option
    $$(page.container).find('.page-content').append('<a href="#" class="add-option searchbar-not-found">Add New Option</a>');
});

var mainView = app.views.create('.view-main', {
  url: '/',
  stackPages: true,
  pushState: true,
  routes: [
    {
	  path: '/search/',
	  async(routeTo, routeFrom, resolve, reject) {
		    //console.log(routeTo, routeFrom);
			var dt = data['_search'];
			dt.category = routeTo.query.category;
			dt.pageTitle = routeTo.query.title;
						
			app.request.json('search.php?action=getdlms&category='+routeTo.query.category, function(data){
				dt.dlms = data;
				resolve({template: templates['searchpage'](dt)});
				return;
				toastCenter = app.toast.create({
				  text: 'Your download will be available in 30 seconds.',
				  position: 'center',
				  closeTimeout: 2000,
				}).open();
				
			});

			return;
  	  }
	},
	
	{
	  path: '/settings/',
	  async(){
		  console.log('gg');
		  app.tab.show("#view-settings")
	  }
	},

	{
	  path: '/browse/',
	  async(routeTo, routeFrom, resolve, reject) {		
		resolve({ template: templates[data['_browse'].tpl](data['_browse'])});
      }
	},
	
	{
	  path: '/dosearch/',
	  url: 'search.php?action=search',
	  options: {
        context: {
          users: ['John Doe', 'Vladimir Kharlampidi', 'Timo Ernst'],
        },
      },
	},
	
	{
	  path: '(.*)',
	  async(routeTo, routeFrom, resolve, reject) {
		console.log(routeTo, routeFrom, app.views.main.router.url);

		if(typeof routeFrom.path === "undefined"){
			resolve({template: templates[data['main'].tpl](data['main'])});
			return;
		}
		else if(routeTo.hash && data[routeTo.hash]){
			resolve({template: templates[data[routeTo.hash].tpl](data[routeTo.hash])});
			return;
		}
		else
			reject();	
		//resolve({ template: templates.navpage(data[routeTo.hash])});
	        //resolve({ pageName: routeTo.hash });
      }
	}
  ],
});

app.views.create('#view-settings', {
  url: '/settings/',
  stackPages: true,
  pushState: true,

  routes: [
    {
      path: '/general/',
      pageName: 'general'
    },
    {
      path: '/',
      async(){
	console.log('gg');
	app.tab.show(".view-main");
      }
    },	  
  ],	
});

function searchString(formid){
	var res = app.form.convertToData('#'+formid);
	console.log(res);
	
	$$('form.form-ajax-submit').on('formajax:success', function (e, data, xhr) {
	  var xhr = e.detail.xhr; // actual XHR object

	  //var data = e.detail.data; // Ajax response from action file
	  
	});

}


var lastTimeBackPress=0;
var timePeriodToExit=2000;

function onBackKeyDown(e){
    e.preventDefault();
    e.stopPropagation();
    if(new Date().getTime() - lastTimeBackPress < timePeriodToExit){
        navigator.app.exitApp();
    }else{
        window.plugins.toast.showWithOptions(
            {
              message: "Press again to exit.",
              duration: "short", // which is 2000 ms. "long" is 4000. Or specify the nr of ms yourself.
              position: "bottom",
              addPixelsY: -40  // added a negative value to move it up a bit (default 0)
            }
          );
        
        lastTimeBackPress=new Date().getTime();
    }
};

