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
    <div class="row" id="schedule-container"></div>
  </div>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
      <script>
        class ScheduleRenderer {
    constructor(data) {
        this.data = data;
    }

    generateTabs() {
        let tabsHtml = '';
        let contentHtml = '';

        let tabCounter = 1;
        for (const date in this.data) {
            const formattedDate = new Date(date).toLocaleDateString("en-US", { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });

            tabsHtml += `
                <button class="nav-link px-4 text-start mb-3 ${tabCounter === 1 ? 'active' : ''}" id="d${tabCounter}-tab" data-bs-toggle="tab" data-bs-target="#day${tabCounter}" type="button" role="tab" aria-controls="day${tabCounter}" aria-selected="${tabCounter === 1 ? 'true' : 'false'}">
                    <span class="d-block fs-5 fw-bold">Day ${tabCounter}</span>
                    <span class="small">${formattedDate}</span>
                </button>
            `;

            const records = this.data[date].rows;
            let scheduleItemsHtml = '';

            for (const time in records) {
                const timeRecords = records[time].records;
                for (const record of timeRecords) {
                    const startTime = new Date(record.date).toLocaleTimeString("en-US", { hour: 'numeric', minute: '2-digit' });
                    const endTime = new Date(new Date(record.date).getTime() + (record.duration * 60 * 1000)).toLocaleTimeString("en-US", { hour: 'numeric', minute: '2-digit' });

                    scheduleItemsHtml += `
                        <li class="d-flex flex-column flex-md-row py-4">
                            <span class="flex-shrink-0 width-13x me-md-4 d-block mb-3 mb-md-0 small text-muted">${startTime} - ${endTime}</span>
                            <div class="flex-grow-1 ps-4 border-start border-3">
                                <h4>${record.product_id}</h4>
                                <p class="mb-0">${record.available} available, ${record.booked} booked</p>
                            </div>
                        </li>
                    `;
                }
            }

            contentHtml += `
                <div class="tab-pane fade ${tabCounter === 1 ? 'active show' : ''}" id="day${tabCounter}" role="tabpanel" aria-labelledby="d${tabCounter}-tab">
                    <ul class="pt-4 list-unstyled mb-0">
                        ${scheduleItemsHtml}
                    </ul>
                </div>
            `;

            tabCounter++;
        }

        return { tabsHtml, contentHtml };
    }

    render() {
        const { tabsHtml, contentHtml } = this.generateTabs();

        const container = document.createElement('div');
        container.className = 'container py-9 py-lg-11 position-relative z-index-1';

        const row = document.createElement('div');
        row.className = 'row';
        container.appendChild(row);

        const leftColumn = document.createElement('div');
        leftColumn.className = 'col-lg-5 mb-5 mb-lg-0';
        row.appendChild(leftColumn);

        leftColumn.innerHTML = `
            <h5 class="mb-4 text-info aos-init aos-animate" data-aos="fade-up">Schedule and Agenda</h5>
            <div class="nav nav-pills flex-column aos-init aos-animate" id="myTab" role="tablist" data-aos="fade-up">
                ${tabsHtml}
            </div>
        `;

        const rightColumn = document.createElement('div');
        rightColumn.className = 'col-lg-7 col-xl-6';
        row.appendChild(rightColumn);

        rightColumn.innerHTML = `
            <div data-aos="fade-up" class="tab-content aos-init aos-animate" id="myTabContent">
                ${contentHtml}
            </div>
        `;

        return container;
    }
}
        const form = document.getElementById('date-range-form');
        form.addEventListener('submit', function(event) {
          event.preventDefault();
          const minDate = document.getElementById('mindate').value;
          const maxDate = document.getElementById('maxdate').value;
          document.getElementById('container').innerHTML = '';
          fetchProductIds(minDate, maxDate);
        });
        // Function to create the container element
        const createContainer = () => {
          const container = document.createElement('div');
          container.classList.add('d-flex', 'flex-wrap', 'gap-2', 'justify-content-center', 'p-2');

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
          const {
            name,
            permalink,
            person_types,
            price,
            price_html,
            qty,
            duration_unit,
            product_id,
            booked,
            available,
            date,
            duration
          } = data;
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

        const fetchProductIds = async (minDate, maxDate) => {
          try {
            const fetchUrl = `https://www.DOMAIN.com/wp-json/wc-bookings/v1/products/slots/?min_date=${minDate}&max_date=${maxDate}&per_page=100`;
            const response = await fetch(fetchUrl);

            if (!response.ok) {
              throw new Error('Error fetching product data');
            }
            const data = await response.json();
/*
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
*/
const groupRecords = (data) => {
  const groupedRecords = {};

  data.records.forEach(record => {
    const [date, time] = record.date.split('T');
    const [year, month, day] = date.split('-');
    const [hour, minute] = time.split(':');

    const realdate = `${day}/${month}/${year}`;
    const realtime = `${hour}:${minute}`;

    if (!groupedRecords[realdate]) {
      groupedRecords[realdate] = {
        count: 0,
        rows: {},
      };
    }

    if (!groupedRecords[realdate].rows[realtime]) {
      groupedRecords[realdate].rows[realtime] = {
        count: 0,
        records: [],
      };
    }

    groupedRecords[realdate].count++;
    groupedRecords[realdate].rows[realtime].count++;
    groupedRecords[realdate].rows[realtime].records.push(record);
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
            for (i = 0; i < data.records.length - 1; i++) {
              let productData = null;

              if (1 === 1) {
                const productUrl = `https://www.midlandsrollerarena.com/wp-json/wc-bookings/v1/products/${data.records[i].product_id}?consumer_key=ck_6b29eadb5c1971dee93ee84c6ee4ea57bf7d2ff4&consumer_secret=cs_1da576dd3d757c7f3538ea94cd9fa86530b15179`;
                const productResponse = await fetch(productUrl);
                productData = await productResponse.json();
              }

              // Extract the additional information for each product
              const {
                product_id,
                booked,
                available,
                duration,
                date
              } = data.records[i];

              // Create a box element with the product data
              const box = createBox({
                ...productData,
                product_id,
                booked,
                available,
                duration,
                date
              });

              // Append the box to the container element
              container.appendChild(box);
              document.querySelector('.spinner-border').style.display = 'none';
              // Hide the spinner and show the content once the product data is fetched
              box.querySelector('.spinner-border').style.display = 'none';
              box.querySelector('.card-body').style.display = 'block';
              box.querySelector('.card-body').style.opacity = '1';

            }
           // const scheduleRenderer = new ScheduleRenderer(groupedResponse);
//const scheduleContainer = document.getElementById('schedule-container');
//scheduleContainer.appendChild(scheduleRenderer.render());
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
