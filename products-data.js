async function csrfFetch(url,options= {
}
) {
  const token=await getCsrfToken();
  options.credentials='same-origin';
  options.headers= {
    ...(options.headers|| {
    }
    ),'X-CSRF-Token':token
  }
  ;
  return fetch(url,options);
}
// ============ PRODUCT API CLIENT ============
const PRODUCTS_ENDPOINT = 'api/products.php';
async function parseErrorResponse(res) {
  try {
    const body=await res.json();
    if(body.errors)return body.errors.join(' ');
    if(body.error)return body.error;
  } catch(e) {
  }
  return `Request failed (${res.status}).`;
}
async function getProducts() {
  const res=await fetch(PRODUCTS_ENDPOINT, {
    credentials:'same-origin'
  }
  );
  if(!res.ok)throw new Error(await parseErrorResponse(res));
  return res.json();
}
async function addProduct(product) {
  const options= {
    method:'POST',credentials:'same-origin'
  }
  ;
  if(product instanceof FormData) {
    options.body=product;
  } else {
    options.headers= {
      'Content-Type':'application/json'
    }
    ;
    options.body=JSON.stringify(product);
  }
  const res=await csrfFetch(PRODUCTS_ENDPOINT,options);
  if(!res.ok)throw new Error(await parseErrorResponse(res));
  return res.json();
}
async function updateProduct(id,updates) {
  const options= {
    method:'PUT',credentials:'same-origin'
  }
  ;
  if(updates instanceof FormData) {
    options.body=updates;
  } else {
    options.headers= {
      'Content-Type':'application/json'
    }
    ;
    options.body=JSON.stringify(updates);
  }
  const res=await csrfFetch(`${PRODUCTS_ENDPOINT}?id=${encodeURIComponent(id)}`,options);
  if(!res.ok)throw new Error(await parseErrorResponse(res));
  return res.json();
}
async function deleteProduct(id) {
  const res=await csrfFetch(`${PRODUCTS_ENDPOINT}?id=${encodeURIComponent(id)}`, {
    method:'DELETE'
  }
  );
  if(!res.ok)throw new Error(await parseErrorResponse(res));
  return res.json();
}
async function resetProductsToDefault() {
  const res=await csrfFetch(`${PRODUCTS_ENDPOINT}?action=reset`, {
    method:'POST'
  }
  );
  if(!res.ok)throw new Error(await parseErrorResponse(res));
  return res.json();
}
