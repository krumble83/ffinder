var app = new Framework7({
  root: '#app',
  name: 'My App',
  id: 'com.myapp.test',
  panel: {
    swipe: 'left',
  },
  
  routes: [
    {
      path: '/about/',
      url: 'about.html',
    },
	{
      path: '/aboutz/',
      url: 'about.html',
    },
  ],
  // ... other parameters
});

var mainView = app.views.create('.view-main', {
  url: '/',
  stackPages: true,
  routes: [
    {
      path: '/',
      pageName: 'casa'
    },
    {
      path: '/dos/',
      pageName: 'dos'
    },
  ],
});