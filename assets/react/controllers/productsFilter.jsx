import React, { useState, useEffect } from "react";

const ProductsFilter = (props) => {
  // Debug: Log products from props when component mounts
  console.log("ProductsFilter props:", props);
  console.log("Products count:", props.products ? props.products.length : 0);

  // Initialize with products from props
  const [products, setProducts] = useState(props.products || []);
  const [filteredProducts, setFilteredProducts] = useState(
    props.products || []
  );
  const [filter, setFilter] = useState("");

  // Component starts with products available, no loading needed
  const [loading, setLoading] = useState(false);

  // Update products state when props change
  useEffect(() => {
    setProducts(props.products || []);
    setFilteredProducts(props.products || []);
  }, [props.products]);

  // Apply filter when filter selection changes
  useEffect(() => {
    applyFilter();
  }, [filter, products]);

  const applyFilter = () => {
    let sortedProducts = [...products];
    switch (filter) {
      case "price-asc":
        sortedProducts.sort((a, b) => a.price - b.price);
        break;
      case "price-desc":
        sortedProducts.sort((a, b) => b.price - a.price);
        break;
      case "alphabetical":
        sortedProducts.sort((a, b) => a.name.localeCompare(b.name));
        break;
      case "alpha-famille":
        sortedProducts.sort((a, b) => a.famille.localeCompare(b.famille));
        break;
      case "date-desc":
        sortedProducts.sort(
          (a, b) => new Date(b.createdAt.date) - new Date(a.createdAt.date)
        );
        break;
      case "date-asc":
        sortedProducts.sort(
          (a, b) => new Date(a.createdAt.date) - new Date(b.createdAt.date)
        );
        break;
      default:
        break;
    }
    setFilteredProducts(sortedProducts);
  };

  const handleFilterChange = (event) => {
    setFilter(event.target.value);
  };

  return (
    <div className="w-100 d-flex align-items-center flex-column">
      {loading && (
        <div className="wrapper">
          <div className="rubik-loader"></div>
        </div>
      )}
      {!loading && (
        <>
          <h3 className="text-center w-100 mt-4">Filtres</h3>
          <div className="d-flex flex-row py-lg-2 w-75 mx-auto">
            <fieldset className="form-group mx-auto">
              <label htmlFor="filter" className="form-label my-4">
                Trier par
              </label>
              <select
                id="filter"
                value={filter}
                onChange={handleFilterChange}
                className="form-control"
              >
                <option value="">Sélectionner</option>
                <option value="price-asc">Prix croissant</option>
                <option value="price-desc">Prix décroissant</option>
                <option value="alphabetical">Ordre alphabétique</option>
                <option value="alpha-famille">Famille</option>
                <option value="date-desc">Plus récent</option>
                <option value="date-asc">Plus ancien</option>
              </select>
            </fieldset>
          </div>

          <div className="container gallery">
            {filteredProducts.map((product) => (
              <div key={product.id} className="card mb-3">
                <h3 className="card-head">{product.name}</h3>
                <div className="card-body">
                  <img
                    className="img-product"
                    src={`${props.imagePathPrefix}${product.image1}`}
                    alt="Le complément alimentaire du futur"
                  />
                  <p className="card-title">{product.description}</p>
                </div>
                <div className="card-foot">
                  {product.price} euros
                  <a
                    href={`${props.productUrlPattern}${product.id}`}
                    className=" btn-gallery"
                  >
                    Voir la fiche
                  </a>
                </div>
              </div>
            ))}
          </div>
        </>
      )}
    </div>
  );
};

export default ProductsFilter;
