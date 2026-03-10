import '@opentelemetry/api-logs';
import loggerProvider from './logger.js';

const logger = loggerProvider.getLogger('default', '1.0.0');

const originalConsole = {
	log: console.log,
	info: console.info,
	warn: console.warn,
	error: console.error,
	debug: console.debug
};

const SeverityNumber = {
	DEBUG: 5,
	INFO: 9,
	WARN: 13,
	ERROR: 17
};

function serializeMessage(...args: unknown[]): string {
	return args
		.map((arg) => (typeof arg === 'object' ? JSON.stringify(arg) : String(arg)))
		.join(' ');
}

console.log = function (...args: unknown[]) {
	logger.emit({
		severityNumber: SeverityNumber.INFO,
		severityText: 'INFO',
		body: serializeMessage(...args),
		attributes: {}
	});
	originalConsole.log.apply(console, args);
};

console.info = function (...args: unknown[]) {
	logger.emit({
		severityNumber: SeverityNumber.INFO,
		severityText: 'INFO',
		body: serializeMessage(...args),
		attributes: {}
	});
	originalConsole.info.apply(console, args);
};

console.warn = function (...args: unknown[]) {
	logger.emit({
		severityNumber: SeverityNumber.WARN,
		severityText: 'WARN',
		body: serializeMessage(...args),
		attributes: {}
	});
	originalConsole.warn.apply(console, args);
};

console.error = function (...args: unknown[]) {
	logger.emit({
		severityNumber: SeverityNumber.ERROR,
		severityText: 'ERROR',
		body: serializeMessage(...args),
		attributes: {}
	});
	originalConsole.error.apply(console, args);
};

console.debug = function (...args: unknown[]) {
	logger.emit({
		severityNumber: SeverityNumber.DEBUG,
		severityText: 'DEBUG',
		body: serializeMessage(...args),
		attributes: {}
	});
	originalConsole.debug.apply(console, args);
};
