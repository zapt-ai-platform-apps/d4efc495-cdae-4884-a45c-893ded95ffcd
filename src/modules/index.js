import { eventBus, events as coreEvents } from './core/events';
import { initializeCore } from './core/internal/initialize';
import { initializeUI } from './ui/internal/initialize';
import { initializeApp } from './app/internal/initialize';

/**
 * Initialize all modules in the application
 * This function is called once at application startup
 */
export async function initializeModules() {
  console.log('Initializing all modules...');
  
  try {
    // Initialize modules in dependency order
    await initializeCore();
    await initializeUI();
    await initializeApp();
    
    // Signal that all modules are initialized
    eventBus.publish(coreEvents.APP_INITIALIZED, { 
      timestamp: new Date(),
      success: true 
    });
    
    console.log('All modules initialized successfully');
    return true;
  } catch (error) {
    console.error('Failed to initialize modules:', error);
    return false;
  }
}