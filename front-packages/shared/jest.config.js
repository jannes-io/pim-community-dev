module.exports = {
  preset: 'ts-jest',
  moduleNameMapper: {
    '\\.(svg|css)$': '<rootDir>/tests/fileMock.ts',
  },
  setupFilesAfterEnv: ['<rootDir>/tests/setupTests.ts'],
  testMatch: ['<rootDir>/src/**/?(*.)+(unit).ts?(x)'],
  collectCoverage: true,
  collectCoverageFrom: ['<rootDir>/src/**/*.{ts,tsx}'],
  coverageReporters: ['text-summary', 'html'],
  coverageDirectory: 'coverage'
};
