/* allowing for asynchronous side-effects on actions or resolvers */

interface FetchOptions {
  body?: any;
  headers?: { [key: string]: string };
}

export const fetch = (path: string, options: FetchOptions = {}) => {
	if (options.body) {
	  options.body = JSON.stringify(options.body);
	  options.headers = { "Content-Type": "application/json" };
	}
	return {
	  type: "FETCH",
	  path,
	  options
	};
  };

  export default {
	FETCH({ path, options }) {
	  return new Promise((resolve, reject) => {
		window
		  .fetch(path, options)
		  .then(response => response.json())
		  .then(result => resolve(result))
		  .catch(error => reject(error));
	  });
	}
  };
