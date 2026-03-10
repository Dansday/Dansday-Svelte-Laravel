import {
	LoggerProvider,
	BatchLogRecordProcessor
} from '@opentelemetry/sdk-logs';
import { OTLPLogExporter } from '@opentelemetry/exporter-logs-otlp-http';
import { resourceFromAttributes } from '@opentelemetry/resources';
import { ATTR_SERVICE_NAME } from '@opentelemetry/semantic-conventions';

const resource = resourceFromAttributes({
	[ATTR_SERVICE_NAME]: process.env.OTEL_SERVICE_NAME || 'dansday-main'
});

const logExporter = new OTLPLogExporter({});

const loggerProvider = new LoggerProvider({
	resource,
	processors: [new BatchLogRecordProcessor(logExporter)]
});

export default loggerProvider;
