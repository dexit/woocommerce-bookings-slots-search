<?php
// get some php here
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

  <style>
    .box {
      background-color: #f2f2f2;
      border: 1px solid #ccc;
      padding: 10px;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
      <div class="col-12">

      <form id="date-range-form">
        <div class="input-group mb-3">
          <span class="input-group-text">Start Date</span>
          <input type="text" id="mindate" class="form-control datepicker" placeholder="Select start date" autocomplete="off">
        </div>
        <div class="input-group mb-3">
          <span class="input-group-text">End Date</span>
          <input type="text" id="maxdate" class="form-control datepicker" placeholder="Select end date" autocomplete="off">
        </div>
        <button type="submit" id="submit-button" class="btn btn-primary">Submit</button>
</form>
</div>
    </div>
    <div class="row">
        <div id="container" class="col-12"></div>
       
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
        const form = document.getElementById('date-range-form');
    form.addEventListener('submit', function(event) {
      event.preventDefault();
      const minDate = document.getElementById('mindate').value;
      const maxDate = document.getElementById('maxdate').value;
      document.getElementById('container').innerHTML = '';
  fetchProductIds(minDate,maxDate);
    });
    // Function to create the container element
    const createContainer = () => {
      const container = document.createElement('div');
      container.classList.add('d-flex', 'flex-wrap', 'gap-2','justify-content-center', 'p-2');

      // Append the container to the document body
      document.getElementById('container').appendChild(container);
// spinner add
        const spinner = document.createElement('div');
        spinner.classList.add('spinner-border', 'text-primary');
        spinner.setAttribute('role', 'status');
        spinner.innerHTML = '<span class="visually-hidden">Loading...</span>';
        container.appendChild(spinner);

      return container;
    }

    // Function to create a box element with the product data
    const createBox = (data) => {
      const { name, permalink, person_types, price, price_html, qty, duration_unit,product_id, booked, available, date, duration } = data;
      const datedd = new Date(`${date.split('T')[0]}`);

const day = datedd.getDate().toString().padStart(2, '0');
const month = (datedd.getMonth() + 1).toString().padStart(2, '0');
const year = datedd.getFullYear();

const formattedDate = `${day}/${month}/${year}`;
      const box = document.createElement('div');
      box.classList.add('card', 'box', 'col-md-2');
      box.setAttribute('data-date', formattedDate);
      box.setAttribute('data-duration', duration);
      box.setAttribute('data-duration_unit', duration_unit);
      box.setAttribute('data-qty', qty);
      box.setAttribute('data-available', available);
      box.setAttribute('data-booked', booked);
      box.setAttribute('data-time', date.split('T')[1]);
      box.setAttribute('data-price', price);
      box.setAttribute('data-url', permalink);
      box.setAttribute('data-product', product_id);
      box.setAttribute('data-name', name);
      // Create a spinner element
      const spinner = document.createElement('div');
      spinner.classList.add('spinner-border', 'text-primary');
      spinner.setAttribute('role', 'status');
      spinner.innerHTML = '<span class="visually-hidden">Loading...</span>';

      // Append the spinner to the box element
      box.appendChild(spinner);
     
      // Create the content for the box element
      const content = document.createElement('div');
      content.classList.add('card-body');
      content.setAttribute('data-date', formattedDate);
      content.style.opacity = '0';
      content.style.transition = 'all 180ms ease-in-out';
      content.style.display = 'none'; // Hide the content initially
     

      content.innerHTML = `
      <a class="card-text price link" data-price="${price}" href="${permalink}">${name}</a>
      <div class="col-md-12" style="
    display: inline-flex;
    justify-content: space-between;
">
      <p class="card-text col-md-6 date text-left" data-date="${formattedDate}">${formattedDate}</p>
      <p class="card-text col-md-6 time text-right" data-time="${date.split('T')[1]}">${date.split('T')[1]}</p>
      </div>

        <p class="card-text qty" data-qty="${qty}"></p>
        <p class="card-text duration" data-duration="${duration}" data-duration_unit="${duration_unit}">Duration: ${duration} / ${duration_unit}</p>
        <p class="card-text availability" data-available="${available}" data-booked="${booked}">Available: ${available} Booked: ${booked}</p>
      `;

      // Append the content to the box element
      box.appendChild(content);

      return box;
    }

const fetchProductIds = async (minDate,maxDate) => {
  try {
    const fetchUrl = `https://www.midlandsrollerarena.com/wp-json/wc-bookings/v1/products/slots/?min_date=${minDate}&max_date=${maxDate}&per_page=100`;
    const response = await fetch(fetchUrl);

    if (!response.ok) {
      throw new Error('Error fetching product data');
    }
    const data = await response.json();
    
  const groupRecords = (response) => {
  const groupedRecords = {};

  response.records.forEach(record => {
    const [date, time] = record.date.split('T');
    const [year, month, day] = date.split('-');
    const [hour, minute] = time.split(':');

    const realdate = `${day}/${month}/${year}`;
    const realtime = `${hour}:${minute}`;

    if (!groupedRecords[realdate]) {
      groupedRecords[realdate] = {};
    }

    if (!groupedRecords[realdate][realtime]) {
      groupedRecords[realdate][realtime] = [];
    }

    groupedRecords[realdate][realtime].push(record);
  });

  return groupedRecords;
};
const groupedResponse = groupRecords(data);
console.log(groupedResponse);

    //const productIds = await data.records.map(entry => entry.product_id);
    // Display results
    //createContainer();
    // Fetch the product data for each product ID
    const container = createContainer();
    for (i = 0; i < data.records.length; i++) {
      let productData = null;

      if (1 === 1) {
        const productUrl = `https://www.midlandsrollerarena.com/wp-json/wc-bookings/v1/products/${data.records[i].product_id}?consumer_key=KEY&consumer_secret=SECRET`;
        const productResponse = await fetch(productUrl);
        productData = await productResponse.json();
      }

      // Extract the additional information for each product
      const { product_id, booked, available, duration, date } = data.records[i];

      // Create a box element with the product data
      const box = createBox({ ...productData, product_id, booked, available, duration, date });

      // Append the box to the container element
      container.appendChild(box);
      document.querySelector('.spinner-border').style.display = 'none';
      // Hide the spinner and show the content once the product data is fetched
      box.querySelector('.spinner-border').style.display = 'none';
      box.querySelector('.card-body').style.display = 'block';
      box.querySelector('.card-body').style.opacity = '1';
   
    }
  } catch (error) {
    console.error('Error fetching product data:', error);
  }
}
          // Call the fetchProductIds function to start the process
//const updateData = (minDate,maxDate) => {
  //  const minDate = document.getElementById('mindate').value;
 //     const maxDate = document.getElementById('maxdate').value;
 // wipe previous data
   

//}

//add range datepicker bs5
$(document).ready(function() {
      $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
      });
    });




</script>
</body>
</html>

booked
available
date
duration
