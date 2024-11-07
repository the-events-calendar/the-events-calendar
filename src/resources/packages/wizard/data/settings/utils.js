export const getResourcePath = (id) => {
	console.log(id);
	console.log(window.resourceAddress);
	const root = `${window.resourceAddress}`;
	return root;
	//return id ? `${root}/${id}` : root;
  };
