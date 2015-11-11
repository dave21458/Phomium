#include "include/phomium_js_object_transfer.h"
//#include "include/phomium_menus.h"
#include "include/phomium_js_query_handler.h"



namespace phomium_js_object_transfer {

	namespace{
	//CefString exc = "failed";
	// Handle bindings in the render process.
		class RenderDelegate : public ClientApp::RenderDelegate {
			public:
			RenderDelegate() {}

			virtual void OnContextCreated(CefRefPtr<ClientApp> app,
									  CefRefPtr<CefBrowser> browser,
									  CefRefPtr<CefFrame> frame,
									  CefRefPtr<CefV8Context> context) OVERRIDE {
			}

			 private:
			  IMPLEMENT_REFCOUNTING(RenderDelegate);
		

		virtual void OnWebKitInitialized(CefRefPtr<ClientApp> app) OVERRIDE {
			// Create the renderer-side router for query handling.
			std::string extensionCodeEnd =
				":\" + menuname,"
				"onSuccess: function(){phomium.result = 1;},"
				"onFailure: function(){phomium.result = 0;},"
				"});};";

			std::string extensionCode =
				"var phomium;"
				"if (!phomium)"
				"  phomium = {};"
				" phomium.result = 0;"
				" phomium.resultText = '';"
				" phomium.resultArray = [];"
				" phomium.timer;"
				" phomium.cb ={};"
				" phomium.resultClear = "
					" function() {"
					" phomium.result = 0;while(phomium.resultArray.length > 0){phomium.resultArray.pop();}phomium.resultText = ''"
					" };"
				" phomium.wait = "
					"function(cb){"
					"if(Object.prototype.toString.call(cb)!='[object Function]')cb = function(){};"
					"phomium.cb = cb;"
					"phomium.result = -1;"
					"phomium.timer = setInterval(function(){phomium.clearWait();},100);"
					"};"
				"phomium.clearWait =" 
					"function(){"
					"if(phomium.result >= 0){"
					"clearInterval(phomium.timer);"
					"phomium.cb();"
					"}};";
			extensionCode +=
				"phomium.test_connect = "
					"function() {"
					"window.cefQuery({"
					"request: \"" ;
				extensionCode +=  std::to_string((_ULonglong)TestConnect);
			extensionCode +=
					":\" + document.title,"
					"onSuccess: function(data){phomium.result = 1;phomium.resultText = data;},"
					"onFailure: function(){phomium.result = 0;},"
					"});};";
			extensionCode +=
				"phomium.disable_menu_state = "
					"function(menuname,wait,cb) {"
					"if(wait)phomium.wait(cb);"
					"window.cefQuery({"
					"request: \"" ;
				extensionCode +=  std::to_string((_ULonglong)DisableMenu) + extensionCodeEnd;
			extensionCode +=
				"phomium.enable_menu_state = "
					"function(menuname,wait,cb) {"
					"if(wait)phomium.wait(cb);"
					"window.cefQuery({"
					"request: \"" ;
				extensionCode +=  std::to_string((_ULonglong)EnableMenu) + extensionCodeEnd;
			extensionCode +=
				"phomium.change_context_menu = "
					"function(menuname,wait,cb) {"
					"if(wait)phomium.wait(cb);"
					"window.cefQuery({"
					"request: \"" ;
				extensionCode +=  std::to_string((_ULonglong)ChangeContextMenu) + extensionCodeEnd;
			extensionCode +=
				"phomium.GetOpenFileName = "
					"function(title,types,dfltName,wait,cb) {"
					"if(wait)phomium.wait(cb);"
					"window.cefQuery({"
					"request: \"" ;
				extensionCode +=  std::to_string((_ULonglong)FileOpenDialog);
				extensionCode +=
					":\" + title + \" &\" + types + \" &\" + dfltName ,"
					"onSuccess: function(data){phomium.resultArray = data.split('\"');phomium.resultText = phomium.resultArray.shift();phomium.result = phomium.resultArray.length},"
					"onFailure: function(){phomium.resultClear();}"
				"});};";
			extensionCode +=
				"phomium.GetSaveFileName = "
					"function(title,types,dfltName,wait,cb) {"
					"if(wait)phomium.wait(cb);"
					"window.cefQuery({"
					"request: \"" ;
				extensionCode +=  std::to_string((_ULonglong)FileSaveDialog);
				extensionCode +=
					":\" + title + \" &\" + types + \" &\" + dfltName ,"
					"onSuccess: function(data){phomium.resultArray = data.split('\"');phomium.resultText = phomium.resultArray.shift();phomium.result = phomium.resultArray.length},"
					"onFailure: function(){phomium.resultClear();}"
				"});};";
			extensionCode +=
				"phomium.openWindow = "
					"function(windowType,windowName,location){"
					"window.cefQuery({"
					"request: \"" ;
				extensionCode +=  std::to_string((_ULonglong)NewWindow);
				extensionCode +=
					":\" + windowType + \" &\" + windowName + \" &\" + location,"
					"onSuccess: function(data){phomium.resultText = data;phomium.result = 1;},"
					"onFailure: function(){phomium.result = 0;}"
				"});};";
				// Register the extension.
			CefRegisterExtension("v8/test", extensionCode, NULL);

		  }
	    };

	} // end anon namespace
void CreateRenderDelegates(ClientApp::RenderDelegateSet& delegates) {
  delegates.insert(new RenderDelegate);
}
}//end namespace

