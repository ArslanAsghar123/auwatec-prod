// Import all necessary Storefront plugins
import BfnDebitPlugin from './bfnDebit-plugin/bfn-debit.plugin';

// Register your plugin via the existing PluginManager
const PluginManager = window.PluginManager;
PluginManager.register('BfnDebitPlugin', BfnDebitPlugin,  '[restrict-order-filter]');
