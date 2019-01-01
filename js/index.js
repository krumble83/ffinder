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
  return data['_strings'][name];
});


var app  = new Framework7({
  root: '#app',
  pushState: true,
  swipePanel: 'left',
  swipeActiveArea: 20,
  on: {
    init: function (page) {
	  templates.navpage = Template7.compile($$('#navpage').html());
	  templates.searchpage = Template7.compile($$('#searchpage').html());
	  templates.navbrowse = Template7.compile($$('#navbrowse').html());
	  Template7.registerPartial('navbar', $$('#navbar').html());
	  
	  return;
	  var quit = true;
	  window.onpopstate = function(event) {
		  alert(event.state, quit);
		  if(event.state == null && quit)
			  alert('quit');
		  else if(event.state == null)
			  quit = true;
		  else
			  quit = false;
		};
    }
  }
});

app.panel.enableSwipe('left');

var mainView = app.views.create('.view-main', {
  url: '/',
  stackPages: true,
  pushState: "true",
  routes: [
    {
	  path: '/search/',
	  async(routeTo, routeFrom, resolve, reject) {
		    console.log(routeTo, routeFrom);
			var dt = data['_search'];
			
			dt.dlms = {};
			
			app.request.json('search.php?action=getdlms&type=search&category=', function(data){
				dt.dlms = data;
				dt.category = routeTo.query.category;
				dt.pageTitle = routeTo.query.title;
				//console.log(dt);
				resolve({template: templates['searchpage'](dt)});
				
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
			resolve({ template: templates[data['main'].tpl](data['main'])});
			return;
		}
		if(!routeTo.hash)
			return;

		//data[routeTo.hash]['pageName'] = routeTo.hash
		resolve({ template: templates[data[routeTo.hash].tpl](data[routeTo.hash])});
		//resolve({ template: templates.navpage(data[routeTo.hash])});
        //resolve({ pageName: routeTo.hash });
      }
	}
  ],
});

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

