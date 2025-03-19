/**
 * EventBus for module communication
 * Provides a pub/sub mechanism for loose coupling between modules
 */

export class EventBus {
  subscribers = {};

  subscribe(event, callback) {
    if (!this.subscribers[event]) this.subscribers[event] = [];
    this.subscribers[event].push(callback);
    return () => this.unsubscribe(event, callback);
  }

  publish(event, data) {
    if (!this.subscribers[event]) return;
    this.subscribers[event].forEach(callback => callback(data));
  }

  unsubscribe(event, callback) {
    if (!this.subscribers[event]) return;
    this.subscribers[event] = this.subscribers[event]
      .filter(cb => cb !== callback);
  }
}

export const eventBus = new EventBus();

export const events = {
  // Core event definitions
  APP_INITIALIZED: 'app/initialized',
};