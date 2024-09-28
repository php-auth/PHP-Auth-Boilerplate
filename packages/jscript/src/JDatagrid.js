/*
| ------------------------------------------------------------------------------
| class Datagrid - http://tabulator.info
| ------------------------------------------------------------------------------
| var columns = [
|   {title: "ID", field: "id", width: 80},
|   {title: "User", field: "username"}
| ];
|
| // http://tabulator.info/docs/5.0/filter
| var select = function (option) {
|     switch (option) {
|       case "value-1":
|         datagrid.filter("field_name", "=", 1);
|         break;
|       case "value-2":
|         datagrid.filter("field_name", "=", 2);
|         break;
|       case "value-3":
|         datagrid.filter("field_name", "=", 3);
|     }
| };
|
| var datagrid = new Datagrid("#datagrid", columns, select);
| ==============================================================================
| HTML
| ==============================================================================
| <form action="/url/to/get/data/json" id="datagrid">
|
|   <select>
|     <option selected disabled hidden>Filter by:</option>
|     <optgroup label="User">
|       <option value="verified-1">Verified</option>
|       <option value="verified-0">Not verified</option>
|     </optgroup>
|   </select>
|
|   <button type="reset">
|     <i class="fa fa-sync-alt mx-2" aria-hidden="true"></i>
|   </button>
|
|   <input type="text" name="term">
|
|   <button type="submit">
|     <i class="fa fa-search mx-2"></i>
|   </button>
|
| </form>
| ------------------------------------------------------------------------------
*/
class JDatagrid {

    /**
     * Class constructor
     *
     * @param {String} id
     * @param {Object} columns
     * @param {Function} select
     * @access public
     */
    constructor(id, columns, select) {
        var form       = document.getElementById(id.split("#")[1]);
        var div        = document.createElement("div");
        this.tabulator = null;
        div.setAttribute("class", "mt-4");
        div.setAttribute("id", form.getAttribute("id") + "_tabulator");
        form.appendChild(div);
        form.getData = () => {
            $_['http'].request(form.getAttribute("action"), (error, response) => {
                this.table(id + "_tabulator", columns, response.data);
            });
        };
        form.getData();
        form.getElementsByTagName("select")[0].addEventListener("change", e => {
            select(e.target.value);
        });
        form.addEventListener("reset", () => {
            this.tabulator.clearFilter();
            form.getData();
        });
        form.addEventListener("submit", e => { // Search field
            $_['http'].request(form.getAttribute("action"), 'term=' + e.target.term.value,
            (error, response) => {
                if (!error) {
                    this.table(id + "_tabulator", columns, response.data);
                }
            });
            e.preventDefault();
        });
    }

    /**
     * Tabulator
     *
     * @param {String} id
     * @param {Object} columns
     * @param {Object} data
     * @access public
     */
    table(id, columns, data) {
        this.tabulator = new Tabulator(id, {
            columns: columns,
            data: data,
            autoColumns: false,
            layout: "fitColumns",
            pagination: "local",
            paginationSize: 10,
            locale: false,
            langs: this.language()
        });
        return this.tabulator;
    }

    /**
     * Tabulator filter
     *
     * @param {Array} ...args
     * @access public
     */
    filter(...args) {
        this.tabulator.setFilter(...args);
    }

    /**
     * Translation
     *
     * @access public
     */
    language() {
        return {
            "default": {
                "ajax": {
                    "loading": translate("Loading..."),
                    "error": translate("Error")
                },
                "groups": {
                    "item": translate("Item"),
                    "items": translate("Items")
                },
                "pagination": {
                    "page_size": translate("Page size"),
                    "first": translate("First"),
                    "first_title": translate("First page"),
                    "last": translate("Last"),
                    "last_title": translate("Last page"),
                    "prev": translate("Previous"),
                    "prev_title": translate("Previous page"),
                    "next": translate("Next"),
                    "next_title": translate("Next page")
                },
                "headerFilters": {
                    "default": translate("Filter column"),
                    "columns": {
                        "name": translate("Filter name")
                    }
                }
            }
        }
    }
}
