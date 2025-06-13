// pH Sensor structure and declarations
struct PHSensor {
    int pin;
    int samples;
    int interval;
    float calibration[3][2] = {
        {4.01, 3300},  // pH 4.01 = ADC 3300 (high voltage = acidic)
        {6.86, 2048},  // pH 6.86 = ADC 2048 (mid voltage = neutral)
        {9.18, 1024}   // pH 9.18 = ADC 1024 (low voltage = basic)
    };
    unsigned long lastRead = 0;
    float lastValue = 0;
    bool isConfigured = false;
};

// Maximum number of pH sensors we can handle
const int MAX_PH_SENSORS = 8;
PHSensor phSensors[MAX_PH_SENSORS];
int numPhSensors = 0;

// Function declarations
float readPH(PHSensor &sensor);
int findPhSensor(int pin);
bool addPhSensor(int pin, int samples, int interval);
void updatePhCalibration(int pin, float cal4, float cal7, float cal10); 