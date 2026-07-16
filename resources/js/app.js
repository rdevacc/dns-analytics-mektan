import './bootstrap';

import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';

import './pages/dns-queries/index';
import './pages/dashboard/index';

window.DataTable = DataTable;