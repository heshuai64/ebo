Ext.app.App = function(config){
    Ext.apply(this, config);
    
    this.addEvents({
        'ready' : true,
        'beforeunload' : true
    });

    Ext.onReady(this.initApp, this);
};

Ext.extend(Ext.app.App, Ext.util.Observable, {
    /**
     * Read-only. This app's ready state
     * @type boolean
     */
    isReady : false,
    /**
     * Read-only. This app's launchers
     * @type object
     */
    launchers : null,
    /**
     * Read-only. This app's modules
     * @type array
     */
    modules : null,
     /**
     * Read-only. This app's styles
     * @type object
     */
    styles : null,
    /**
     * Read-only. This app's Start Menu config
     * @type object
     */
    startConfig : null,
    /**
     * Read-only. This app's Start Menu's items and toolItems configs.
     * @type object
     */
    startItemsConfig : null,
    /**
     * Read-only. This app's logout button config
     * @type object
     */
    logoutButtonConfig : null,
    /**
	 * Read-only. The url of this app's server connection
	 * 
	 * Allows a module to connect to its server script without knowing the path.
	 * Example ajax call:
	 * 
	 * Ext.Ajax.request({
	 * 		url: this.app.connection,
	 * 		// Could also pass moduleId and fileName in querystring like this,
	 * 		// instead of in the params config option.
	 *      
	 * 		// url: this.app.connection+'?moduleId='+this.id+'&fileName=Preferences.php',
	 *      params: {
	 *			moduleId: this.id,
	 *			fileName: 'Preferences.php',
	 *
	 *			...
	 *		},
	 *		success: function(){
	 *			...
	 *		},
	 *		failure: function(){
	 *			...
	 *		},
	 *		scope: this
	 *	});
	 */
	connection : 'connect.php',
	/**
     * Read-only. The url of this app's loader script
     * @type string
     * 
     * Handles module on demand loading
     */
	loader : 'load.php',
	/**
	 * Read-only. The queue of requests to run once a module is loaded
	 */
	requestQueue : [],
	
	init : Ext.emptyFn,
	startMenuSortFn : Ext.emptyFn,
	getModules : Ext.emptyFn,
	getLaunchers : Ext.emptyFn,
	getStyles : Ext.emtpyFn,
	getStartConfig : Ext.emptyFn,
    getLogoutConfig : Ext.emptyFn,
    
	initApp : function(){
		Ext.BLANK_IMAGE_URL = 'resources/images/default/s.gif';

    	this.preventBackspace();
    	
    	this.privileges = this.privileges || this.getPrivileges();
    	
    	this.modules = this.modules || this.getModules();
    	this.initModules();
		
		this.startConfig = this.startConfig || this.getStartConfig();
		this.startItemsConfig = this.startItemsConfig || this.getStartItemsConfig();
		Ext.apply(this.startConfig, this.startItemsConfig);
		
		this.desktop = new Ext.Desktop(this);
		
		this.styles = this.styles || this.getStyles();
    	this.initStyles();
		
		this.launchers = this.launchers || this.getLaunchers();
		this.initLaunchers();
		
		this.logoutConfig = this.logoutConfig || this.getLogoutConfig();
		this.initLogout();
		
		this.init();
	
		Ext.EventManager.on(window, 'beforeunload', this.onBeforeUnload, this);
		this.fireEvent('ready', this);
		this.isReady = true;
    },
    
    initLogout : function(){
		if(this.logoutConfig){
			this.desktop.taskbar.startMenu.addTool(this.logoutConfig);
		}
	},

	initStyles : function(){
    	var s = this.styles;
    	if(!s){
    		return false;
    	}
    	
    	this.desktop.setBackgroundColor(s.backgroundcolor);
    	this.desktop.setFontColor(s.fontcolor);
    	this.desktop.setTheme(s.theme);
    	this.desktop.setTransparency(s.transparency);
    	this.desktop.setWallpaper(s.wallpaper);
    	this.desktop.setWallpaperPosition(s.wallpaperposition);
    	
    	return true;
    },

    initModules : function(){
    	var ms = this.modules;
    	if(!ms){ return false; }
    	
		for(var i = 0, len = ms.length; i < len; i++){			
			if(ms[i].loaded === true){
				//ms[i].app = this;
    		}else{
    			// module is not loaded, set the handler for its launcher
    			ms[i].launcher.handler = this.createWindow.createDelegate(this, [ms[i].moduleId]);
    		}
    		ms[i].app = this;
        }
        
        return true;
    },
    
    initLaunchers : function(){
    	var l = this.launchers;
    	if(!l){
    		return false;
    	}
    	
    	if(l.contextmenu){
			this.initContextMenu(l.contextmenu);
		}
		if(l.quickstart){
			this.initQuickStart(l.quickstart);
		}
		if(l.shortcut){
			this.initShortcut(l.shortcut);
		}
		if(l.autorun){
			this.onReady(this.initAutoRun.createDelegate(this, [l.autorun]), this);
		}
		
		return true;
    },
    
    /**
	 * @param {array} mIds An array of the module ids to run when this app is ready
	 */
    initAutoRun : function(mIds){
    	if(mIds){
    		for(var i = 0, len = mIds.length; i < len; i++){
	            var m = this.getModule(mIds[i]);
	            if(m){
	            	m.autorun = true;
	            	this.createWindow(mIds[i]);
	            }
			}
		}
    },

	/**
	 * @param {array} mIds An array of the module ids to add to the Desktop Context Menu
	 */
    initContextMenu : function(mIds){
    	if(mIds){
    		for(var i = 0, len = mIds.length; i < len; i++){
    			this.desktop.addContextMenuItem(mIds[i]);
	        }
    	}
    },

	/**
	 * @param {array} mIds An array of the module ids to add to the Desktop Shortcuts
	 */
    initShortcut : function(mIds){
		if(mIds){
			for(var i = 0, len = mIds.length; i < len; i++){
	            this.desktop.addShortcut(mIds[i], false);
	        }
		}
    },

	/**
	 * @param {array} mIds An array of the modulId's to add to the Quick Start panel
	 */
	initQuickStart : function(mIds){
		if(mIds){
			for(var i = 0, len = mIds.length; i < len; i++){
	            this.desktop.addQuickStartButton(mIds[i], false);
	        }
		}
    },
    
    /**
	 * Returns the Start Menu items and toolItems configs
	 * @param {array} ms An array of the modules.
	 */
	getStartItemsConfig : function(){
		var ms = this.modules;
		var sortFn = this.startMenuSortFn;
		
    	if(ms){
    		var paths;
    		var root;
    		var sm = { menu: { items: [] } }; // Start Menu
    		var smi = sm.menu.items;
    		
    		smi.push({text: 'StartMenu', menu: { items: [] } });
    		smi.push({text: 'ToolMenu', menu: { items: [] } });
    		
			for(var i = 0, iLen = ms.length; i < iLen; i++){ // loop through modules
				if(ms[i].menuPath){	
					paths = ms[i].menuPath.split('/');
					root = paths[0];
					
					if(paths.length > 0){
						if(root === 'StartMenu'){
							simplify(smi[0].menu, paths, ms[i].launcher);
							sort(smi[0].menu);
						}else if(root === 'ToolMenu'){
							simplify(smi[1].menu, paths, ms[i].launcher);
							sort(smi[1].menu);
						}
					}
				}
			}
			
			return {
				items: smi[0].menu.items,
				toolItems: smi[1].menu.items
			};
    	}
    	
    	return null;
		
		/**
		 * Creates nested arrays that represent the Start Menu.
		 * 
		 * @param {array} pMenu The Start Menu
		 * @param {array} texts The menu texts
		 * @param {object} launcher The launcher config
		 */
		function simplify(pMenu, paths, launcher){
			var newMenu;
			var foundMenu;
			
			for(var i = 1, len = paths.length; i < len; i++){ // ignore the root (StartMenu, ToolMenu)
				foundMenu = findMenu(pMenu.items, paths[i]); // text exists?
				
				if(!foundMenu){
					newMenu = {
						iconCls: 'ux-start-menu-submenu',
						handler: function(){ return false; },
						menu: { items: [] },
						text: paths[i]
					};
					pMenu.items.push(newMenu);
					pMenu = newMenu.menu;
				}else{
					pMenu = foundMenu;
				}
			}
			
			pMenu.items.push(launcher);
		}
		
		/**
		 * Returns the menu if found.
		 * 
		 * @param {array} pMenu The parent menu to search
		 * @param {string} text
		 */
		function findMenu(pMenu, text){
			for(var j = 0, jlen = pMenu.length; j < jlen; j++){
				if(pMenu[j].text === text){
					return pMenu[j].menu; // found the menu, return it
				}
			}
			return null;
		}
		
		/**
		 * @param {array} menu The nested array to sort
		 */
		function sort(menu){
			var items = menu.items;
			for(var i = 0, ilen = items.length; i < ilen; i++){
				if(items[i].menu){
					sort(items[i].menu); // use recursion to iterate nested arrays
				}
				bubbleSort(items, 0, items.length); // sort the menu items
			}
		}
		
		/**
		 * @param {array} items Menu items to sort
		 * @param {integer} start The start index
		 * @param {integer} stop The stop index
		 */
		function bubbleSort(items, start, stop){
			for(var i = stop - 1; i >= start;  i--){
				for(var j = start; j <= i; j++){
					if(items[j+1] && items[j]){
						if(sortFn(items[j], items[j+1])){
							var tempValue = items[j];
							items[j] = items[j+1];
							items[j+1] = tempValue;
						}

					}
				}
			}
			return items;
		}
	},
    
    /**
     * @param {string} moduleId
     * 
     * Provides the handler to the placeholder's launcher until the module it is loaded.
     * Requests the module.  Passes in the callback and scope as params.
     */
    createWindow : function(moduleId){
    	var m = this.requestModule(moduleId, function(m){
    		if(m){
	    		m.createWindow();
	    	}
    	}, this);
    },
    
    /** 
     * @param {string} v The moduleId or moduleType you want returned
     * @param {Function} cb The Function to call when the module is ready/loaded
     * @param {object} scope The scope in which to execute the function
     */
	requestModule : function(v, cb, scope){
    	var m = this.getModule(v);
        
        if(m){
	        if(m.loaded === true){
	        	cb.call(scope, m);
	        }else{
	        	if(cb && scope){
		        	this.requestQueue.push({
			        	moduleId: m.moduleId,
			        	callback: cb,
			        	scope: scope
			        });
			        this.loadModule(m.moduleId, m.launcher.text);
	        	}
	        }
        }
    },
    
    loadModule : function(moduleId, moduleName){
    	var notifyWin = this.desktop.showNotification({
			html: 'Loading ' + moduleName + '...'
			, title: 'Please wait'
		});
    	
    	Ext.Ajax.request({
    		url: this.loader,
    		params: {
    			moduleId: moduleId
    		},
    		success: function(o){
    			notifyWin.setIconClass('x-icon-done');
				notifyWin.setTitle('Finished');
				notifyWin.setMessage(moduleName + ' loaded.');
				this.desktop.hideNotification(notifyWin);
				notifyWin = null;
		
    			if(o.responseText !== ''){
    				eval(o.responseText);
    				this.loadModuleComplete(true, moduleId);
    			}else{
    				alert('An error occured on the server.');
    			}
    		},
    		failure: function(){
    			alert('Connection to the server failed!');
    		},
    		scope: this
    	});
    },
    
    /**
     * @param {boolean} success
     * @param {string} moduleId
     * 
     * Will be called when a module is loaded.
     * If a request for this module is waiting in the
     * queue, it as executed and removed from the queue.
     */
    loadModuleComplete : function(success, moduleId){    	
    	if(success === true && moduleId){
    		var m = this.getModule(moduleId);
    		m.loaded = true;
    		m.init();
    		
	    	var q = this.requestQueue;
	    	var nq = [];
	    	for(var i = 0, len = q.length; i < len; i++){
	    		if(q[i].moduleId === moduleId){
	    			q[i].callback.call(q[i].scope, m);
	    		}else{
	    			nq.push(q[i]);
	    		}
	    	}
	    	this.requestQueue = nq;
    	}
    },

    /**
     * @param {string} v The moduleId or moduleType you want returned
     */
    getModule : function(v){
    	var ms = this.modules;
    	
        for(var i = 0, len = ms.length; i < len; i++){
    		if(ms[i].moduleId == v || ms[i].moduleType == v){
    			return ms[i];
			}
        }
        
        return null;
    },
    
    
    /**
     * @param {Ext.app.Module} m The module to register
     */
    registerModule: function(m){
    	if(!m){ return false; }
		this.modules.push(m);
		m.launcher.handler = this.createWindow.createDelegate(this, [m.moduleId]);
		m.app = this;
	},

    /**
     * @param {string} moduleId or moduleType 
     * @param {array} requests An array of request objects
     * 
     * Example:
     * this.app.makeRequest('module-id', {
	 *    requests: [
	 *       {
	 *          action: 'createWindow',
	 *          params: '',
	 *          callback: this.myCallbackFunction,
	 *          scope: this
	 *       },
	 *       { ... }
	 *    ]
	 * });
     */
    makeRequest : function(moduleId, requests){
    	if(moduleId !== '' && Ext.isArray(requests)){
	    	var m = this.requestModule(moduleId, function(m){
	    		if(m){
		    		m.handleRequest(requests);
		    	}
	    	}, this);
    	}
    },
    
    /**
     * @param {string} action The module action
     * @param {string} moduleId The moduleId property
     */
    isAllowedTo : function(action, moduleId){
    	var p = this.privileges,
    		a = p[action];
    	
    	if(p && a){
    		for(var i = 0, len = a.length; i < len; i++){
    			if(a[i] === moduleId){
    				return true;
    			}
    		}
    	}
    	
    	return false;
    },
    
    getDesktop : function(){
        return this.desktop;
    },
    
    /**
     * @param {Function} fn The function to call after the app is ready
     * @param {object} scope The scope in which to execute the function
     */
    onReady : function(fn, scope){
        if(!this.isReady){
            this.on('ready', fn, scope);
        }else{
            fn.call(scope, this);
        }
    },
    
    onBeforeUnload : function(e){
        if(this.fireEvent('beforeunload', this) === false){
            e.stopEvent();
        }
    },
    
    /**
     * Prevent the backspace (history -1) shortcut
     */
    preventBackspace : function(){
    	var map = new Ext.KeyMap(document, [{
			key: Ext.EventObject.BACKSPACE,
			fn: function(key, e){
				var t = e.target.tagName;
				if(t != "INPUT" && t != "TEXTAREA"){
					e.stopEvent();
				}
			}
		}]);
    }
});

//----------------------------------------------------------------------------

Ext.Desktop = function(app){
	
	this.taskbar = new Ext.ux.TaskBar(app);
	var taskbar = this.taskbar;
	
	this.el = Ext.get('x-desktop');
	var desktopEl = this.el;
	
    var taskbarEl = Ext.get('ux-taskbar');
    
    this.shortcuts = new Ext.ux.Shortcuts({
    	renderTo: 'x-desktop',
    	taskbarEl: taskbarEl
    });
	
    var windows = new Ext.WindowGroup();
    windows.zseed = 10000;
    var activeWindow;
		
    function minimizeWin(win){
        win.minimized = true;
        win.hide();
    }

    function markActive(win){
        if(activeWindow && activeWindow != win){
            markInactive(activeWindow);
        }
        taskbar.setActiveButton(win.taskButton);
        activeWindow = win;
        Ext.fly(win.taskButton.el).addClass('active-win');
        win.minimized = false;
    }

    function markInactive(win){
        if(win == activeWindow){
            activeWindow = null;
            Ext.fly(win.taskButton.el).removeClass('active-win');
        }
    }

    function removeWin(win){
    	taskbar.taskButtonPanel.remove(win.taskButton);
        layout();
    }

    function layout(){
    	desktopEl.setHeight(Ext.lib.Dom.getViewHeight() - taskbarEl.getHeight());
    }
    Ext.EventManager.onWindowResize(layout);

    this.layout = layout;

    this.createWindow = function(config, cls){
    	var win = new (cls||Ext.Window)(
            Ext.applyIf(config||{}, {
                manager: windows,
                minimizable: true,
                maximizable: true
            })
        );
        win.render(desktopEl);
        win.taskButton = taskbar.taskButtonPanel.add(win);

        win.cmenu = new Ext.menu.Menu({
            items: [

            ]
        });

        win.animateTarget = win.taskButton.el;
        
        win.on({
        	'activate': {
        		fn: markActive
        	},
        	'beforeshow': {
        		fn: markActive
        	},
        	'deactivate': {
        		fn: markInactive
        	},
        	'minimize': {
        		fn: minimizeWin
        	},
        	'close': {
        		fn: removeWin
        	}
        });
        
        layout();
        return win;
    };

    this.getManager = function(){
        return windows;
    };

    this.getWindow = function(id){
        return windows.get(id);
    };
    
    this.getViewHeight = function(){
    	return (Ext.lib.Dom.getViewHeight()-taskbarEl.getHeight());
    };
    
    this.getViewWidth = function(){
    	return Ext.lib.Dom.getViewWidth();
    };
    
    this.getWinWidth = function(){
		var width = Ext.lib.Dom.getViewWidth();
		return width < 200 ? 200 : width;
	};
		
	this.getWinHeight = function(){
		var height = (Ext.lib.Dom.getViewHeight()-taskbarEl.getHeight());
		return height < 100 ? 100 : height;
	};
		
	this.getWinX = function(width){
		return (Ext.lib.Dom.getViewWidth() - width) / 2
	};
		
	this.getWinY = function(height){
		return (Ext.lib.Dom.getViewHeight()-taskbarEl.getHeight() - height) / 2;
	};
	
	this.setBackgroundColor = function(hex){
		if(hex){
			Ext.get(document.body).setStyle('background-color', '#'+hex);
			app.styles.backgroundcolor = hex;
		}
	};
	
	this.setFontColor = function(hex){
		if(hex){
			Ext.util.CSS.updateRule('.ux-shortcut-btn-text', 'color', '#'+hex);
			app.styles.fontcolor = hex;
		}
	};
	
	this.setTheme = function(o){
		if(o && o.id && o.name && o.pathtofile){
			Ext.util.CSS.swapStyleSheet('theme', o.pathtofile);
			app.styles.theme = o;
		}
	};
	
	this.setTransparency = function(v){
		if(v >= 0 && v <= 100){
			taskbarEl.addClass("transparent");
			Ext.util.CSS.updateRule('.transparent','opacity', v/100);
			Ext.util.CSS.updateRule('.transparent','-moz-opacity', v/100);
			Ext.util.CSS.updateRule('.transparent','filter', 'alpha(opacity='+v+')');
			
			app.styles.transparency = v;
		}
	};
	
	this.setWallpaper = function(o){
		if(o && o.id && o.name && o.pathtofile){
			
			var notifyWin = this.showNotification({
				html: 'Loading wallpaper...'
				, title: 'Please wait'
			});
			
			var wp = new Image();
			wp.src = o.pathtofile;
			
			var task = new Ext.util.DelayedTask(verify, this);
			task.delay(200);
			
			app.styles.wallpaper = o;
		}
		
		function verify(){
			if(wp.complete){
				task.cancel();
				
				notifyWin.setIconClass('x-icon-done');
				notifyWin.setTitle('Finished');
				notifyWin.setMessage('Wallpaper loaded.');
				this.hideNotification(notifyWin);
				
				document.body.background = wp.src;
			}else{
				task.delay(200);
			}
		}
	};
	
	this.setWallpaperPosition = function(pos){
		if(pos){
			if(pos === "center"){
				var b = Ext.get(document.body);
				b.removeClass('wallpaper-tile');
				b.addClass('wallpaper-center');
			}else if(pos === "tile"){
				var b = Ext.get(document.body);
				b.removeClass('wallpaper-center');
				b.addClass('wallpaper-tile');
			}			
			app.styles.wallpaperposition = pos;
		}
	};
	
	this.showNotification = function(config){
		var win = new Ext.ux.Notification(Ext.apply({
			animateTarget: taskbarEl
			, autoDestroy: true
			, hideDelay: 5000
			, html: ''
			, iconCls: 'x-icon-waiting'
			, title: ''
		}, config));
		win.show();

		return win;
	};
	
	this.hideNotification = function(win, delay){
		if(win){
			(function(){ win.animHide(); }).defer(delay || 3000);
		}
	};
	
	this.addAutoRun = function(id){
		var m = app.getModule(id),
			c = app.launchers.autorun;
			
		if(m && !m.autorun){
			m.autorun = true;
			c.push(id);
		}
	};
	
	this.removeAutoRun = function(id){
		var m = app.getModule(id),
			c = app.launchers.autorun;
			
		if(m && m.autorun){
			var i = 0;
				
			while(i < c.length){
				if(c[i] == id){
					c.splice(i, 1);
				}else{
					i++;
				}
			}
			
			m.autorun = null;
		}
	};
	
	// Private
	this.addContextMenuItem = function(id){
		var m = app.getModule(id);
		if(m && !m.contextMenuItem){
			/* if(m.moduleType === 'menu'){ // handle menu modules
				var items = m.items;
				for(var i = 0, len = items.length; i < len; i++){
					m.launcher.menu.items.push(app.getModule(items[i]).launcher);
				}
			} */
			this.cmenu.add(m.launcher);
		}
	};

	this.addShortcut = function(id, updateConfig){
		var m = app.getModule(id);
		
		if(m && !m.shortcut){
			var c = m.launcher;
			
			m.shortcut = this.shortcuts.addShortcut({
				handler: c.handler,
				iconCls: c.shortcutIconCls,
				scope: c.scope,
				text: c.text,
				tooltip: c.tooltip || ''
			});
			
			if(updateConfig){
				app.launchers.shortcut.push(id);
			}
		}
		
	};

	this.removeShortcut = function(id, updateConfig){
		var m = app.getModule(id);
		
		if(m && m.shortcut){
			this.shortcuts.removeShortcut(m.shortcut);
			m.shortcut = null;
			
			if(updateConfig){
				var sc = app.launchers.shortcut,
					i = 0;
				while(i < sc.length){
					if(sc[i] == id){
						sc.splice(i, 1);
					}else{
						i++;
					}
				}
			}
		}
	};

	this.addQuickStartButton = function(id, updateConfig){
    	var m = app.getModule(id);
    	
		if(m && !m.quickStartButton){
			var c = m.launcher;
			
			m.quickStartButton = this.taskbar.quickStartPanel.add({
				handler: c.handler,
				iconCls: c.iconCls,
				scope: c.scope,
				text: c.text,
				tooltip: c.tooltip || c.text
			});
			
			if(updateConfig){
				app.launchers.quickstart.push(id);
			}
		}
    };
    
    this.removeQuickStartButton = function(id, updateConfig){
    	var m = app.getModule(id);
    	
		if(m && m.quickStartButton){
			this.taskbar.quickStartPanel.remove(m.quickStartButton);
			m.quickStartButton = null;
			
			if(updateConfig){
				var qs = app.launchers.quickstart,
					i = 0;
				while(i < qs.length){
					if(qs[i] == id){
						qs.splice(i, 1);
					}else{
						i++;
					}
				}
			}
		}
    };

    layout();
    
    this.cmenu = new Ext.menu.Menu();
    
    desktopEl.on('contextmenu', function(e){
    	if(e.target.id === desktopEl.id){
	    	e.stopEvent();
			if(!this.cmenu.el){
				this.cmenu.render();
			}
			var xy = e.getXY();
			xy[1] -= this.cmenu.el.getHeight();
			this.cmenu.showAt(xy);
		}
	}, this);
};

//----------------------------------------------------------------------------
Ext.namespace("Ext.ux.form");

Ext.ux.form.HexField = Ext.extend(Ext.form.TextField,  {
	initEvents : function(){
		Ext.ux.form.HexField.superclass.initEvents.call(this);
		
		var keyPress = function(e){
			var k = e.getKey();
			
			if(!Ext.isIE && (e.isSpecialKey() || k == e.BACKSPACE || k == e.DELETE)){
				return;
			}
			
			// allowed text characters
			var allowed = '0123456789abcdefABCDEF';
			
			// get selected text length
			var selection = new Ext.ux.form.HexField.Selection( document.getElementById(this.id) );
			var s = selection.create();
			var selLength = s.end - s.start;
			
			// get text character keyed in
			var c = e.getCharCode();
			var c = String.fromCharCode(c);
			var value = c + this.getValue();
			
			if(allowed.indexOf(c) === -1 || (value.length > 6 && selLength === 0) ){
				e.stopEvent();
			}
		};
		this.el.on("keypress", keyPress, this);
	}
	
	, validateValue : function(value){
		if(!Ext.ux.form.HexField.superclass.validateValue.call(this, value)){
			return false;
		}
		if(!/^[0-9a-fA-F]{6}$/.test(value)){
			return false;
		}
		return true;
    }
});
Ext.reg('hexfield', Ext.ux.form.HexField);

Ext.ux.form.HexField.Selection = function(textareaElement) {
    this.element = textareaElement;
};

Ext.ux.form.HexField.Selection.prototype.create = function() {
    if (document.selection != null && this.element.selectionStart == null) {
        return this._ieGetSelection();
    } else {
        return this._mozillaGetSelection();
    }
}

Ext.ux.form.HexField.Selection.prototype._mozillaGetSelection = function() {
    return { 
        start: this.element.selectionStart, 
        end: this.element.selectionEnd 
    };
}

Ext.ux.form.HexField.Selection.prototype._ieGetSelection = function() {
    this.element.focus();

    var range = document.selection.createRange();
    var bookmark = range.getBookmark();

    var contents = this.element.value;
    var originalContents = contents;
    var marker = this._createSelectionMarker();
    while(contents.indexOf(marker) != -1) {
        marker = this._createSelectionMarker();
    }

    var parent = range.parentElement();
    if (parent == null || parent.type != "text") {
        return { start: 0, end: 0 };
    }
    range.text = marker + range.text + marker;
    contents = this.element.value;

    var result = {};
    result.start = contents.indexOf(marker);
    contents = contents.replace(marker, "");
    result.end = contents.indexOf(marker);

    this.element.value = originalContents;
    range.moveToBookmark(bookmark);
    range.select();
	
    return result;
}

Ext.ux.form.HexField.Selection.prototype._createSelectionMarker = function() {
    return "##SELECTION_MARKER_" + Math.random() + "##";
}
//----------------------------------------------------------------------------
Ext.app.Module = function(config){
    Ext.apply(this, config);
    Ext.app.Module.superclass.constructor.call(this);
    //this.init();
}

Ext.extend(Ext.app.Module, Ext.util.Observable, {
	/**
	 * Read only. {object}
	 * Override this with the launcher for your module.
	 * 
	 * Example:
	 * 
	 * {
	 *    iconCls: 'pref-icon',
     *    handler: this.createWindow,
     *    scope: this,
     *    shortcutIconCls: 'pref-shortcut-icon',
     *    text: 'Preferences',
     *    tooltip: '<b>Preferences</b><br />Allows you to modify your desktop'
	 * }
	 */
	launcher : null,
	/**
	 * Read only. {boolean}
	 * Ext.app.App uses this property to determine if the module has been loaded.
	 */
	loaded : false,
	/**
	 * Read only. {string}
	 * Override this with the menu path for your module.
	 * Ext.app.App uses this property to add this module to the Start Menu.
	 * 
	 * Case sensitive options are:
	 * 
	 * 1. StartMenu
	 * 2. ToolMenu
	 * 
	 * Example:
	 * 
	 * menuPath: 'StartMenu/Bogus Menu'
	 */	
	menuPath : 'StartMenu',
	/**
	 * Read only. {string}
	 * Override this with the type of your module.
	 * Example: 'system/preferences'
	 */
	moduleType : null,
	/**
	 * Read only. {string}
	 * Override this with the unique id of your module.
	 */
	moduleId : null,
    /**
     * Override this to initialize your module.
     */
    init : Ext.emptyFn,
    /**
     * Override this function to create your module's window.
     */
    createWindow : Ext.emptyFn,
    /**
	 * @param {array} An array of request objects
	 *
	 * Override this function to handle requests from other modules.
	 * Expect the passed in param to look like the following.
	 * 
	 * {
	 *    requests: [
	 *       {
	 *          action: 'createWindow',
	 *          params: '',
	 *          callback: this.myCallbackFunction,
	 *          scope: this
	 *       },
	 *       { ... }
	 *    ]
	 * }
	 *
	 * View makeRequest() in App.js for more details.
	 */
	handleRequest : Ext.emptyFn
});
//----------------------------------------------------------------------------
Ext.ux.NotificationMgr = {
    positions: []
};

Ext.ux.Notification = Ext.extend(Ext.Window, {
	initComponent : function(){
		Ext.apply(this, {
			iconCls: this.iconCls || 'x-icon-information'
			, width: 200
			, autoHeight: true
			, closable: true
			, plain: false
			, draggable: false
			, bodyStyle: 'text-align:left;padding:10px;'
			, resizable: false
		});
		if(this.autoDestroy){
			this.task = new Ext.util.DelayedTask(this.close, this);
		}else{
			this.closable = true;
		}
		Ext.ux.Notification.superclass.initComponent.call(this);
    }

	, setMessage : function(msg){
		this.body.update(msg);
	}
	
	, setTitle : function(title, iconCls){
        Ext.ux.Notification.superclass.setTitle.call(this, title, iconCls||this.iconCls);
    }

	, onRender : function(ct, position) {
		Ext.ux.Notification.superclass.onRender.call(this, ct, position);
	}

	, onDestroy : function(){
		Ext.ux.NotificationMgr.positions.remove(this.pos);
		Ext.ux.Notification.superclass.onDestroy.call(this);
	}

	, afterShow : function(){
		Ext.ux.Notification.superclass.afterShow.call(this);
		this.on('move', function(){
			Ext.ux.NotificationMgr.positions.remove(this.pos);
			if(this.autoDestroy){
				this.task.cancel();
			}
		}, this);
		if(this.autoDestroy){
			this.task.delay(this.hideDelay || 5000);
		}
	}

	, animShow : function(){
		this.pos = 0;
		while(Ext.ux.NotificationMgr.positions.indexOf(this.pos)>-1){
			this.pos++;
		}
		Ext.ux.NotificationMgr.positions.push(this.pos);
		this.setSize(200,100);
		this.el.alignTo(this.animateTarget || document, "br-tr", [ -1, -1-((this.getSize().height+10)*this.pos) ]);
		this.el.slideIn('b', {
			duration: .7
			, callback: this.afterShow
			, scope: this
		});
	}

	, animHide : function(){
		Ext.ux.NotificationMgr.positions.remove(this.pos);
		this.el.ghost("b", {
			duration: 1
			, remove: true
		});
	}
});
//----------------------------------------------------------------------------
Ext.namespace("Ext.ux");

Ext.ux.Shortcuts = function(config){
	var desktopEl = Ext.get(config.renderTo)
		, taskbarEl = config.taskbarEl
		, btnHeight = 74
		, btnWidth = 64
		, btnPadding = 15
		, col = null
		, row = null
		, items = [];
	
	initColRow();
	
	function initColRow(){
		col = {index: 1, x: btnPadding};
		row = {index: 1, y: btnPadding};
	}
	
	function isOverflow(y){
		if(y > (Ext.lib.Dom.getViewHeight() - taskbarEl.getHeight())){
			return true;
		}
		return false;
	}
	
	this.addShortcut = function(config){
		var div = desktopEl.createChild({tag:'div', cls: 'ux-shortcut-item'}),
			btn = new Ext.ux.ShortcutButton(Ext.apply(config, {
				text: Ext.util.Format.ellipsis(config.text, 25)
			}), div);
		
		//btn.container.initDD('DesktopShortcuts');
		
		items.push(btn);
		this.setXY(btn.container);
		
		return btn;
	};
	
	this.removeShortcut = function(b){
		var d = document.getElementById(b.container.id);
		
		b.destroy();
		d.parentNode.removeChild(d);
		
		var s = [];
		for(var i = 0, len = items.length; i < len; i++){
			if(items[i] != b){
				s.push(items[i]);
			}
		}
		items = s;
		
		this.handleUpdate();
	}
	
	this.handleUpdate = function(){
		initColRow();
		for(var i = 0, len = items.length; i < len; i++){
			this.setXY(items[i].container);
		}
	}
	
	this.setXY = function(item){
		var bottom = row.y + btnHeight,
			overflow = isOverflow(row.y + btnHeight);
		
		if(overflow && bottom > (btnHeight + btnPadding)){
			col = {
				index: col.index++
				, x: col.x + btnWidth + btnPadding
			};
			row = {
				index: 1
				, y: btnPadding
			};
		}
		
		item.setXY([
			col.x
			, row.y
		]);
		
		row.index++;
		row.y = row.y + btnHeight + btnPadding;
	};
	
	Ext.EventManager.onWindowResize(this.handleUpdate, this, {delay:500});
};



/**
 * @class Ext.ux.ShortcutButton
 * @extends Ext.Button
 */
Ext.ux.ShortcutButton = function(config, el){
	
    Ext.ux.ShortcutButton.superclass.constructor.call(this, Ext.apply(config, {
        renderTo: el,
        //clickEvent: 'dblclick',
		template: new Ext.Template(
			'<div class="ux-shortcut-btn"><div>',
				'<img src="'+Ext.BLANK_IMAGE_URL+'" />',
				'<div class="ux-shortcut-btn-text">{0}</div>',
			'</div></div>')
    }));
    
};

Ext.extend(Ext.ux.ShortcutButton, Ext.Button, {

	buttonSelector : 'div:first',
	
    /* onRender : function(){
        Ext.ux.ShortcutButton.superclass.onRender.apply(this, arguments);

        this.cmenu = new Ext.menu.Menu({
            items: [{
                id: 'open',
                text: 'Open',
                //handler: this.win.minimize,
                scope: this.win
            }, '-', {
                id: 'remove',
                iconCls: 'remove',
                text: 'Remove Shortcut',
                //handler: this.closeWin.createDelegate(this, this.win, true),
                scope: this.win
            }]
        });

        this.el.on('contextmenu', function(e){
        	e.stopEvent();
            if(!this.cmenu.el){
                this.cmenu.render();
            }
            var xy = e.getXY();
            xy[1] -= this.cmenu.el.getHeight();
            this.cmenu.showAt(xy);
        }, this);
    }, */
    
    initButtonEl : function(btn, btnEl){
    	Ext.ux.ShortcutButton.superclass.initButtonEl.apply(this, arguments);
    	
    	btn.removeClass("x-btn");
    	
    	if(this.iconCls){
            if(!this.cls){
                btn.removeClass(this.text ? 'x-btn-text-icon' : 'x-btn-icon');
            }
        }
    },
    
    autoWidth : function(){
    	// do nothing
    },
	
	/**
     * Sets this shortcut button's text
     * @param {String} text The button text
     */
    setText : function(text){
        this.text = text;
        if(this.el){
        	this.el.child("div.ux-shortcut-btn-text").update(text);
        }
    }
});
//----------------------------------------------------------------------------
Ext.namespace("Ext.ux");

Ext.ux.StartMenu = function(config){
	Ext.ux.StartMenu.superclass.constructor.call(this, config);
    
    var tools = this.toolItems;
    this.toolItems = new Ext.util.MixedCollection();
    if(tools){
        this.addTool.apply(this, tools);
    }
};

Ext.extend(Ext.ux.StartMenu, Ext.menu.Menu, {
	height : 300,
	toolPanelWidth : 100,
	width : 300,
	
    // private
    render : function(){
        if(this.el){
            return;
        }
        var el = this.el = new Ext.Layer({
            cls: "x-menu ux-start-menu",
            shadow:this.shadow,
            constrain: false,
            parentEl: this.parentEl || document.body,
            zindex:15000
        });
        
        var header = el.createChild({
        	tag: "div" /*,
        	cls: "x-window-header x-unselectable x-panel-icon "//+this.iconCls */
        });
        header.setStyle('padding', '7px 0 0 0');
        
		this.header = header;
		/* Don't create header text span tag.
		 * Can be uncommented.
		var headerText = header.createChild({
			tag: "span",
			cls: "x-window-header-text"
		}); */
		var tl = header.wrap({
			cls: "ux-start-menu-tl"
		});
		var tr = header.wrap({
			cls: "ux-start-menu-tr"
		});
		var tc = header.wrap({
			cls: "ux-start-menu-tc"
		});
		
		this.menuBWrap = el.createChild({
			tag: "div",
			cls: "ux-start-menu-body x-border-layout-ct ux-start-menu-body"
		});
		var ml = this.menuBWrap.wrap({
			cls: "ux-start-menu-ml"
		});
		var mc = this.menuBWrap.wrap({
			cls: "ux-start-menu-mc ux-start-menu-bwrap"
		});
		
		this.menuPanel = this.menuBWrap.createChild({
			tag: "div",
			cls: "x-panel x-border-panel ux-start-menu-apps-panel opaque"
		});
		this.toolsPanel = this.menuBWrap.createChild({
			tag: "div",
			cls: "x-panel x-border-panel ux-start-menu-tools-panel"
		});
		
		var bwrap = ml.wrap({cls: "x-window-bwrap"});
		var bc = bwrap.createChild({
			tag: "div",
			cls: "ux-start-menu-bc"
		});
		var bl = bc.wrap({
			cls: "ux-start-menu-bl x-panel-nofooter"
		});
		var br = bc.wrap({
			cls: "ux-start-menu-br"
		});
		
		bc.setStyle({
			height: '0px',
			padding: '0 0 6px 0'
		});
		
        this.keyNav = new Ext.menu.MenuNav(this);

        if(this.plain){
            el.addClass("x-menu-plain");
        }
        if(this.cls){
            el.addClass(this.cls);
        }
        // generic focus element
        this.focusEl = el.createChild({
            tag: "a",
            cls: "x-menu-focus",
            href: "#",
            onclick: "return false;",
            tabIndex:"-1"
        });
        
        var ul = this.menuPanel.createChild({
        	tag: "ul",
        	cls: "x-menu-list"});
        var toolsUl = this.toolsPanel.createChild({
        	tag: "ul",
        	cls: "x-menu-list"
        });
        
        var ulListeners = {
        	"click": {
        		fn: this.onClick,
        		scope: this
        	},
        	"mouseover": {
        		fn: this.onMouseOver,
        		scope: this
        	},
        	"mouseout": {
        		fn: this.onMouseOut,
        		scope: this
        	}
        };
        
        ul.on(ulListeners);
        
        this.items.each(
        	function(item){
	            var li = document.createElement("li");
	            li.className = "x-menu-list-item";
	            ul.dom.appendChild(li);
	            item.render(li, this);
	        }, this);

        this.ul = ul;
        this.autoWidth();

        toolsUl.on(ulListeners);
        
        this.toolItems.each(
        	function(item){
	            var li = document.createElement("li");
	            li.className = "x-menu-list-item";
	            toolsUl.dom.appendChild(li);
	            item.render(li, this);
	        }, this);
	        
        this.toolsUl = toolsUl;
        this.autoWidth();
             
        this.menuBWrap.setStyle('position', 'relative');  
        this.menuBWrap.setHeight(this.height);
        
        this.menuPanel.setStyle({
        	padding: '2px',
        	position: 'absolute',
        	overflow: 'auto'
        });
        
        this.toolsPanel.setStyle({
        	padding: '2px 4px 2px 2px',
        	position: 'absolute',
        	overflow: 'auto'
        });
        
        this.setTitle(this.title);
    },
    
    // private
    findTargetItem : function(e){
        var t = e.getTarget(".x-menu-list-item", this.ul,  true);
        if(t && t.menuItemId){
        	if(this.items.get(t.menuItemId)){
            	return this.items.get(t.menuItemId);
            }else{
            	return this.toolItems.get(t.menuItemId);
            }
        }
    },

    /**
     * Displays this menu relative to another element
     * @param {Mixed} element The element to align to
     * @param {String} position (optional) The {@link Ext.Element#alignTo} anchor position to use in aligning to
     * the element (defaults to this.defaultAlign)
     * @param {Ext.ux.StartMenu} parentMenu (optional) This menu's parent menu, if applicable (defaults to undefined)
     */
    show : function(el, pos, parentMenu){
        this.parentMenu = parentMenu;
        if(!this.el){
            this.render();
        }

        this.fireEvent("beforeshow", this);
        this.showAt(this.el.getAlignToXY(el, pos || this.defaultAlign), parentMenu, false);
        
        var tPanelWidth = this.toolPanelWidth;      
        var box = this.menuBWrap.getBox();
        this.menuPanel.setWidth(box.width-tPanelWidth);
        this.menuPanel.setHeight(box.height);
        
        this.toolsPanel.setWidth(tPanelWidth);
        this.toolsPanel.setX(box.x+box.width-tPanelWidth);
        this.toolsPanel.setHeight(box.height);
    },
    
    addTool : function(){
        var a = arguments, l = a.length, item;
        for(var i = 0; i < l; i++){
            var el = a[i];
            if(el.render){ // some kind of Item
                item = this.addToolItem(el);
            }else if(typeof el == "string"){ // string
                if(el == "separator" || el == "-"){
                    item = this.addToolSeparator();
                }else{
                    item = this.addText(el);
                }
            }else if(el.tagName || el.el){ // element
                item = this.addElement(el);
            }else if(typeof el == "object"){ // must be menu item config?
                item = this.addToolMenuItem(el);
            }
        }
        return item;
    },
    
    /**
     * Adds a separator bar to the Tools
     * @return {Ext.menu.Item} The menu item that was added
     */
    addToolSeparator : function(){
        return this.addToolItem(new Ext.menu.Separator({itemCls: 'ux-toolmenu-sep'}));
    },

    addToolItem : function(item){
        this.toolItems.add(item);
        if(this.toolsUl){
            var li = document.createElement("li");
            li.className = "x-menu-list-item";
            this.toolsUl.dom.appendChild(li);
            item.render(li, this);
            this.delayAutoWidth();
        }
        return item;
    },

    addToolMenuItem : function(config){
        if(!(config instanceof Ext.menu.Item)){
            if(typeof config.checked == "boolean"){ // must be check menu item config?
                config = new Ext.menu.CheckItem(config);
            }else{
                config = new Ext.menu.Item(config);
                //config = new Ext.menu.Adapter(this.getToolButton(config), {canActivate:true});
            }
        }
        return this.addToolItem(config);
    },
    
    setTitle : function(title, iconCls){
        this.title = title;
        if(this.header.child('span')){
        	this.header.child('span').update(title);
        }
        return this;
    },
    
    getToolButton : function(config){
    	var btn = new Ext.Button({
			handler: config.handler,
			//iconCls: config.iconCls,
			minWidth: this.toolPanelWidth-10,
			scope: config.scope,
			text: config.text
		});
		
    	return btn;
    }
});
//----------------------------------------------------------------------------
Ext.namespace("Ext.ux");

/**
 * @class Ext.ux.TaskBar
 * @extends Ext.util.Observable
 */
Ext.ux.TaskBar = function(app){
    this.app = app;
    this.init();
}

Ext.extend(Ext.ux.TaskBar, Ext.util.Observable, {
    init : function(){
		this.startMenu = new Ext.ux.StartMenu(Ext.apply({
			iconCls: 'user',
			height: 300,
			shadow: true,
			title: 'Todd Murdock',
			width: 300
		}, this.app.startConfig));
		
		this.startButton = new Ext.Button({
            text: 'Start',
            id: 'ux-startbutton',
            iconCls: 'start',
            menu: this.startMenu,
            menuAlign: 'bl-tl',
            renderTo: 'ux-taskbar-start'
        });
        
        var startWidth = Ext.get('ux-startbutton').getWidth() + 10;
        
        var sbBox = new Ext.BoxComponent({
			el: 'ux-taskbar-start',
	        id: 'TaskBarStart',
	        minWidth: startWidth,
			region:'west',
			split: false,
			width: startWidth
		});
		
		this.quickStartPanel = new Ext.ux.QuickStartPanel({
			el: 'ux-quickstart-panel',
	        id: 'TaskBarQuickStart',
	        minWidth: 60,
			region:'west',
			split: true,
			width: 94
		});
		
		this.taskButtonPanel = new Ext.ux.TaskButtonsPanel({
			el: 'ux-taskbuttons-panel',
			id: 'TaskBarButtons',
			region:'center'
		});
		
		/* this.trayPanel = new Ext.ux.QuickStartPanel({ // Todo: make the system tray
			el: 'ux-systemtray-panel',
	        id: 'TaskBarSystemTray',
	        minWidth: 100,
			region:'east',
			split: true,
			width: 180
		}); */
		
		var panelWrap = new Ext.Container({
			el: 'ux-taskbar-panel-wrap',
			items: [this.quickStartPanel,this.taskButtonPanel],
			layout: 'border',
			region: 'center'
		});
				
        var container = new Ext.ux.TaskBarContainer({
			el: 'ux-taskbar',
			layout: 'border',
			items: [sbBox,panelWrap]
		});
		this.el = container.el;
		
		return this;
    },
    
	setActiveButton : function(btn){
		this.taskButtonPanel.setActiveButton(btn);
	}
});



/**
 * @class Ext.ux.TaskBarContainer
 * @extends Ext.Container
 */
Ext.ux.TaskBarContainer = Ext.extend(Ext.Container, {
    initComponent : function() {
        Ext.ux.TaskBarContainer.superclass.initComponent.call(this);
        
        this.el = Ext.get(this.el) || Ext.getBody();
        this.el.setHeight = Ext.emptyFn;
        this.el.setWidth = Ext.emptyFn;
        this.el.setSize = Ext.emptyFn;
        this.el.setStyle({
            overflow:'hidden',
            margin:'0',
            border:'0 none'
        });
        this.el.dom.scroll = 'no';
        this.allowDomMove = false;
        this.autoWidth = true;
        this.autoHeight = true;
        Ext.EventManager.onWindowResize(this.fireResize, this);
        this.renderTo = this.el;
    },

    fireResize : function(w, h){
        this.fireEvent('resize', this, w, h, w, h);
    }
});



/**
 * @class Ext.ux.TaskButtonsPanel
 * @extends Ext.BoxComponent
 */
Ext.ux.TaskButtonsPanel = Ext.extend(Ext.BoxComponent, {
	activeButton: null,
	enableScroll: true,
	scrollIncrement: 0,
    scrollRepeatInterval: 400,
    scrollDuration: .35,
    animScroll: true,
    resizeButtons: true,
    buttonWidth: 168,
    minButtonWidth: 118,
    buttonMargin: 2,
    buttonWidthSet: false,
	
	initComponent : function() {
        Ext.ux.TaskButtonsPanel.superclass.initComponent.call(this);
        this.on('resize', this.delegateUpdates);
        this.items = [];
        
        this.stripWrap = Ext.get(this.el).createChild({
        	cls: 'ux-taskbuttons-strip-wrap',
        	cn: {
            	tag:'ul', cls:'ux-taskbuttons-strip'
            }
		});
        this.stripSpacer = Ext.get(this.el).createChild({
        	cls:'ux-taskbuttons-strip-spacer'
        });
        this.strip = new Ext.Element(this.stripWrap.dom.firstChild);
        
        this.edge = this.strip.createChild({
        	tag:'li',
        	cls:'ux-taskbuttons-edge'
        });
        this.strip.createChild({
        	cls:'x-clear'
        });
	},
	
	add : function(win){
		var li = this.strip.createChild({tag:'li'}, this.edge); // insert before the edge
        var btn = new Ext.ux.TaskBar.TaskButton(win, li);
		
		this.items.push(btn);
		
		if(!this.buttonWidthSet){
			this.lastButtonWidth = btn.container.getWidth();
		}
		
		this.setActiveButton(btn);
		return btn;
	},
	
	remove : function(btn){
		var li = document.getElementById(btn.container.id);
		btn.destroy();
		li.parentNode.removeChild(li);
		
		var s = [];
		for(var i = 0, len = this.items.length; i < len; i++) {
			if(this.items[i] != btn){
				s.push(this.items[i]);
			}
		}
		this.items = s;
		
		this.delegateUpdates();
	},
	
	setActiveButton : function(btn){
		this.activeButton = btn;
		this.delegateUpdates();
	},
	
	delegateUpdates : function(){
		/*if(this.suspendUpdates){
            return;
        }*/
        if(this.resizeButtons && this.rendered){
            this.autoSize();
        }
        if(this.enableScroll && this.rendered){
            this.autoScroll();
        }
    },
    
    autoSize : function(){
        var count = this.items.length;
        var ow = this.el.dom.offsetWidth;
        var aw = this.el.dom.clientWidth;

        if(!this.resizeButtons || count < 1 || !aw){ // !aw for display:none
            return;
        }
        
        var each = Math.max(Math.min(Math.floor((aw-4) / count) - this.buttonMargin, this.buttonWidth), this.minButtonWidth); // -4 for float errors in IE
        var btns = this.stripWrap.dom.getElementsByTagName('button');
        
        this.lastButtonWidth = Ext.get(btns[0].id).findParent('li').offsetWidth;
        
        for(var i = 0, len = btns.length; i < len; i++) {            
            var btn = btns[i];
            
            var tw = Ext.get(btns[i].id).findParent('li').offsetWidth;
            var iw = btn.offsetWidth;
            
            btn.style.width = (each - (tw-iw)) + 'px';
        }
    },
    
    autoScroll : function(){
    	var count = this.items.length;
        var ow = this.el.dom.offsetWidth;
        var tw = this.el.dom.clientWidth;
        
        var wrap = this.stripWrap;
        var cw = wrap.dom.offsetWidth;
        var pos = this.getScrollPos();
        var l = this.edge.getOffsetsTo(this.stripWrap)[0] + pos;
        
        if(!this.enableScroll || count < 1 || cw < 20){ // 20 to prevent display:none issues
            return;
        }
        
        wrap.setWidth(tw); // moved to here because of problem in Safari
        
        if(l <= tw){
            wrap.dom.scrollLeft = 0;
            //wrap.setWidth(tw); moved from here because of problem in Safari
            if(this.scrolling){
                this.scrolling = false;
                this.el.removeClass('x-taskbuttons-scrolling');
                this.scrollLeft.hide();
                this.scrollRight.hide();
            }
        }else{
            if(!this.scrolling){
                this.el.addClass('x-taskbuttons-scrolling');
            }
            tw -= wrap.getMargins('lr');
            wrap.setWidth(tw > 20 ? tw : 20);
            if(!this.scrolling){
                if(!this.scrollLeft){
                    this.createScrollers();
                }else{
                    this.scrollLeft.show();
                    this.scrollRight.show();
                }
            }
            this.scrolling = true;
            if(pos > (l-tw)){ // ensure it stays within bounds
                wrap.dom.scrollLeft = l-tw;
            }else{ // otherwise, make sure the active button is still visible
				this.scrollToButton(this.activeButton, true); // true to animate
            }
            this.updateScrollButtons();
        }
    },

    createScrollers : function(){
        var h = this.el.dom.offsetHeight; //var h = this.stripWrap.dom.offsetHeight;
		
        // left
        var sl = this.el.insertFirst({
            cls:'ux-taskbuttons-scroller-left'
        });
        sl.setHeight(h);
        sl.addClassOnOver('ux-taskbuttons-scroller-left-over');
        this.leftRepeater = new Ext.util.ClickRepeater(sl, {
            interval : this.scrollRepeatInterval,
            handler: this.onScrollLeft,
            scope: this
        });
        this.scrollLeft = sl;

        // right
        var sr = this.el.insertFirst({
            cls:'ux-taskbuttons-scroller-right'
        });
        sr.setHeight(h);
        sr.addClassOnOver('ux-taskbuttons-scroller-right-over');
        this.rightRepeater = new Ext.util.ClickRepeater(sr, {
            interval : this.scrollRepeatInterval,
            handler: this.onScrollRight,
            scope: this
        });
        this.scrollRight = sr;
    },
    
    getScrollWidth : function(){
        return this.edge.getOffsetsTo(this.stripWrap)[0] + this.getScrollPos();
    },

    getScrollPos : function(){
        return parseInt(this.stripWrap.dom.scrollLeft, 10) || 0;
    },

    getScrollArea : function(){
        return parseInt(this.stripWrap.dom.clientWidth, 10) || 0;
    },

    getScrollAnim : function(){
        return {
        	duration: this.scrollDuration,
        	callback: this.updateScrollButtons,
        	scope: this
        };
    },

    getScrollIncrement : function(){
    	return (this.scrollIncrement || this.lastButtonWidth+2);
    },
    
    /* getBtnEl : function(item){
        return document.getElementById(item.id);
    }, */
    
    scrollToButton : function(item, animate){
    	item = item.el.dom.parentNode; // li
        if(!item){ return; }
        var el = item; //this.getBtnEl(item);
        var pos = this.getScrollPos(), area = this.getScrollArea();
        var left = Ext.fly(el).getOffsetsTo(this.stripWrap)[0] + pos;
        var right = left + el.offsetWidth;
        if(left < pos){
            this.scrollTo(left, animate);
        }else if(right > (pos + area)){
            this.scrollTo(right - area, animate);
        }
    },
    
    scrollTo : function(pos, animate){
        this.stripWrap.scrollTo('left', pos, animate ? this.getScrollAnim() : false);
        if(!animate){
            this.updateScrollButtons();
        }
    },
    
    onScrollRight : function(){
        var sw = this.getScrollWidth()-this.getScrollArea();
        var pos = this.getScrollPos();
        var s = Math.min(sw, pos + this.getScrollIncrement());
        if(s != pos){
        	this.scrollTo(s, this.animScroll);
        }        
    },

    onScrollLeft : function(){
        var pos = this.getScrollPos();
        var s = Math.max(0, pos - this.getScrollIncrement());
        if(s != pos){
            this.scrollTo(s, this.animScroll);
        }
    },
    
    updateScrollButtons : function(){
        var pos = this.getScrollPos();
        this.scrollLeft[pos == 0 ? 'addClass' : 'removeClass']('ux-taskbuttons-scroller-left-disabled');
        this.scrollRight[pos >= (this.getScrollWidth()-this.getScrollArea()) ? 'addClass' : 'removeClass']('ux-taskbuttons-scroller-right-disabled');
    }
});



/**
 * @class Ext.ux.TaskBar.TaskButton
 * @extends Ext.Button
 */
Ext.ux.TaskBar.TaskButton = function(win, el){
	this.win = win;
	
    Ext.ux.TaskBar.TaskButton.superclass.constructor.call(this, {
        iconCls: win.iconCls,
        text: Ext.util.Format.ellipsis(win.title, 20),
        tooltip: win.taskbuttonTooltip || win.title,
        renderTo: el,
        handler : function(){
            if(win.minimized || win.hidden){
                win.show();
            }else if(win == win.manager.getActive()){
                win.minimize();
            }else{
                win.toFront();
            }
        },
        clickEvent:'mousedown'
    });
};

Ext.extend(Ext.ux.TaskBar.TaskButton, Ext.Button, {
    onRender : function(){
        Ext.ux.TaskBar.TaskButton.superclass.onRender.apply(this, arguments);

        this.cmenu = new Ext.menu.Menu({
            items: [{
            	id: 'restore',
                text: 'Restore',
                handler: function(){
                    if(!this.win.isVisible()){
                        this.win.show();
                    }else{
                        this.win.restore();
                    }
                },
                scope: this
            },{
                id: 'minimize',
                text: 'Minimize',
                handler: this.win.minimize,
                scope: this.win
            },{
                id: 'maximize',
                text: 'Maximize',
                handler: this.win.maximize,
                scope: this.win
            }, '-', {
                id: 'close',
                text: 'Close',
                handler: this.closeWin.createDelegate(this, this.win, true),
                scope: this.win
            }]
        });

        this.cmenu.on('beforeshow', function(){
            var items = this.cmenu.items.items;
            var w = this.win;
            items[0].setDisabled(w.maximized !== true && w.hidden !== true);
            items[1].setDisabled(w.minimized === true);
            items[2].setDisabled(w.maximized === true || w.hidden === true);
			items[2].setDisabled(w.maximizable === false);
			items[3].setDisabled(w.closable === false);
        }, this);

        this.el.on('contextmenu', function(e){
        	e.stopEvent();
            if(!this.cmenu.el){
                this.cmenu.render();
            }
            var xy = e.getXY();
            xy[1] -= this.cmenu.el.getHeight();
            this.cmenu.showAt(xy);
        }, this);
    },
    
    closeWin : function(cMenu, e, win){
		if(!win.isVisible()){
			win.show();
		}else{
			win.restore();
		}
		win.close();
	},
	
	/**
	 * override so autoWidth() is not called
     * @param {String} text The text for the button
     */
    setText : function(text){
		if(text){
			this.text = text;
			if(this.el){
				this.el.child("td.x-btn-center " + this.buttonSelector).update(Ext.util.Format.ellipsis(text, 20));
			}
		}
    },
    
    /**
     * @param {String/Object} tooltip The tooltip for the button - can be a string or QuickTips config object
     */
    setTooltip : function(text){
    	if(text){
    		this.tooltip = text;
        	var btnEl = this.el.child(this.buttonSelector);
        	Ext.QuickTips.unregister(btnEl.id);
        	
            if(typeof this.tooltip == 'object'){                
                Ext.QuickTips.register(Ext.apply({
                      target: btnEl.id
                }, this.tooltip));
            } else {
            	btnEl.dom[this.tooltipType] = this.tooltip;
            }
        }
    }
});



/**
 * @class Ext.ux.QuickStartPanel
 * @extends Ext.BoxComponent
 */
Ext.ux.QuickStartPanel = Ext.extend(Ext.BoxComponent, {
	enableMenu: true,
	
	initComponent : function(){
        Ext.ux.QuickStartPanel.superclass.initComponent.call(this);
        
        this.on('resize', this.delegateUpdates);
        
        this.menu = new Ext.menu.Menu();
        
        this.items = [];
        
        this.stripWrap = Ext.get(this.el).createChild({
        	cls: 'ux-quickstart-strip-wrap',
        	cn: {tag:'ul', cls:'ux-quickstart-strip'}
		});
		
        this.stripSpacer = Ext.get(this.el).createChild({
        	cls:'ux-quickstart-strip-spacer'
        });
        
        this.strip = new Ext.Element(this.stripWrap.dom.firstChild);
        
        this.edge = this.strip.createChild({
        	tag:'li',
        	cls:'ux-quickstart-edge'
        });
        
        this.strip.createChild({
        	cls:'x-clear'
        });
	},
	
	add : function(config){
		var li = this.strip.createChild({tag:'li'}, this.edge); // insert before the edge
        
		var btn = new Ext.Button(Ext.apply(config, {
			cls:'x-btn-icon',
			menuText: config.text,
			renderTo: li,
			text: '' // do not display text
		}));
        
		this.items.push(btn);
		
		this.delegateUpdates();
		
		return btn;
	},
	
	remove : function(btn){
		var li = document.getElementById(btn.container.id);
		btn.destroy();
		li.parentNode.removeChild(li);
		
		var s = [];
		for(var i = 0, len = this.items.length; i < len; i++) {
			if(this.items[i] != btn){
				s.push(this.items[i]);
			}
		}
		this.items = s;
		
		this.delegateUpdates();
	},
	
	menuAdd : function(config){
		this.menu.add(config);
	},
	
	delegateUpdates : function(){
        if(this.enableMenu && this.rendered){
        	this.showButtons();
        	this.clearMenu();
            this.autoMenu();
        }
    },
    
    showButtons : function(){
    	var count = this.items.length;
    	
    	for(var i = 0; i < count; i++){
			this.items[i].show(); 	
		}
    },
    
    clearMenu : function(){
    	this.menu.removeAll();
    },
	
	autoMenu : function(){
    	var count = this.items.length;
        var ow = this.el.dom.offsetWidth;
        var tw = this.el.dom.clientWidth;
        
        var wrap = this.stripWrap;
        var cw = wrap.dom.offsetWidth;
       	var l = this.edge.getOffsetsTo(this.stripWrap)[0];
        
        if(!this.enableMenu || count < 1 || cw < 20){ // 20 to prevent display:none issues
            return;
        }
        
        wrap.setWidth(tw);
        
        if(l <= tw){
            if(this.showingMenu){
                this.showingMenu = false;
                this.menuButton.hide();
            }
        }else{
        	tw -= wrap.getMargins('lr');
            
            wrap.setWidth(tw > 20 ? tw : 20);
            
            if(!this.showingMenu){
                if(!this.menuButton){
                    this.createMenuButton();
                }else{
                    this.menuButton.show();
                }
            }
            
            mo = this.getMenuButtonPos();
            
            for(var i = count-1; i >= 0; i--){
            	var bo = this.items[i].el.dom.offsetLeft + this.items[i].el.dom.offsetWidth;
            	
            	if(bo > mo){
            		this.items[i].hide();

            		var ic = this.items[i].initialConfig,
            			config = {
	            			iconCls: ic.iconCls,
	            			handler: ic.handler,
	            			scope: ic.scope,
	            			text: ic.menuText
	            		};
            		
            		this.menuAdd(config);
            	}else{
            		this.items[i].show();
            	}
            }
            
            this.showingMenu = true;
        }
    },
    
    createMenuButton : function(){
    	
       	var h = this.el.dom.offsetHeight;

        var mb = this.el.insertFirst({
            cls:'ux-quickstart-menubutton-wrap'
        });
        
        mb.setHeight(h);
        
        var btn = new Ext.Button({
        	cls:'x-btn-icon',
        	id: 'ux-quickstart-menubutton',
        	menu: this.menu,
        	renderTo: mb
        });
        
        mb.setWidth(Ext.get('ux-quickstart-menubutton').getWidth());
        
        this.menuButton = mb;
    },
    
    getMenuButtonPos : function(){
    	return this.menuButton.dom.offsetLeft;
    }
});